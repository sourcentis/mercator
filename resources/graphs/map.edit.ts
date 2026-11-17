import {
    Cell,
    CellEditorHandler,
    type CellStateStyle,
    EventObject,
    FitPlugin,
    Geometry,
    Graph,
    GraphDataModel,
    type GraphPluginConstructor,
    InternalEvent,
    type InternalMouseEvent,
    ModelXmlSerializer,
    PanningHandler,
    Point,
    RubberBandHandler,
    SelectionCellsHandler,
    SelectionHandler,
    styleUtils,
    type UndoableEdit,
    UndoManager,
    VertexHandler,
    VertexHandlerConfig,
} from '@maxgraph/core';
import { downloadGraphSVG } from './svg-export';

//-----------------------------------------------------------------------
// Interfaces métier
// Note: "Node" est volontairement renommé MapNode pour ne pas masquer le DOM Node.

interface Edge {
    attachedNodeId: string;
    name: string;
    edgeType: string;
    edgeDirection: string;
    bidirectional: boolean;
    color: string | null;
}

interface MapNode {
    id: string;
    vue: string;
    label: string;
    title?: string;
    attributes?: string | null;
    order: number;
    image: string;
    type: string;
    edges: Edge[];
}

type NodeMap = Map<string, MapNode>;

// Déclaration de globals fournies par ailleurs
declare const _nodes: NodeMap;

// Drapeaux métier ajoutés au style MaxGraph (absents de CellStateStyle).
interface AppCellStyle extends CellStateStyle {
    isBackground?: boolean;
    isRectangle?: boolean;
    isText?: boolean;
}

function styleOf(cell: Cell | null | undefined): AppCellStyle | undefined {
    return cell?.style as AppCellStyle | undefined;
}

function isBackgroundCell(cell: Cell | null | undefined): boolean {
    return !!styleOf(cell)?.isBackground;
}

function isRectangleCell(cell: Cell | null | undefined): boolean {
    return !!styleOf(cell)?.isRectangle;
}

// Garantit que la cellule de fond reste sous tout le reste après un orderCells.
function ensureBackgroundAtBottom(): void {
    const bg = model.getCell(BACKGROUND_ID) as Cell | null;
    if (bg) graph.orderCells(true, [bg]);
}

function isTextCell(cell: Cell | null | undefined): boolean {
    return !!styleOf(cell)?.isText;
}

//-----------------------------------------------------------------------
// Plugins MaxGraph

const plugins: GraphPluginConstructor[] = [
    // L'ordre est important
    CellEditorHandler,
    SelectionCellsHandler,
    SelectionHandler,
    PanningHandler,
    RubberBandHandler,
    FitPlugin,
];

// Initialisation du graph

const container = document.getElementById('graph-container') as HTMLDivElement | null;
if (!container) {
    throw new Error('#graph-container introuvable');
}

const graph = new Graph(container, new GraphDataModel(), plugins);
const model = graph.getDataModel();

// Rendre le container focusable (tabIndex=-1 : pas dans la séquence Tab,
// mais le focus JS et le clic peuvent le cibler).
container.tabIndex = -1;
container.style.outline = 'none';

// Listeners sur window (pas document) : window est au-dessus de document dans
// la chaîne de propagation, donc ces handlers s'exécutent AVANT tout listener
// MaxGraph ou Select2, même ceux enregistrés en phase capture sur document.
let graphHasFocus = false;
window.addEventListener('mousedown', (e: MouseEvent) => {
    if (container.contains(e.target as Node)) {
        graphHasFocus = true;
        // Focus synchrone (avant que MaxGraph ne traite l'événement)
        container.focus();
        // Focus différé pour couvrir le cas où MaxGraph re-cible le focus
        setTimeout(() => container.focus(), 0);
    } else {
        graphHasFocus = false;
    }
}, true);

//-----------------------------------------------------------------------
// Style des arêtes

const edgeDefaultStyle = graph.getStylesheet().getDefaultEdgeStyle();
edgeDefaultStyle.labelBackgroundColor = '#FFFFFF';
edgeDefaultStyle.strokeWidth = 2;
edgeDefaultStyle.rounded = false;
edgeDefaultStyle.entryPerimeter = false;

// Désactiver le folding
graph.getFoldingImage = () => null;

// Ne pas changer la sélection sur clic droit : EdgeHandler appelle
// selectCellForEvent sans vérifier isPopupTrigger, ce qui viderait la
// multi-sélection. SelectionHandler vérifie déjà isPopupTrigger, donc
// cette surcharge corrige uniquement le cas EdgeHandler.
const _selectCellForEvent = graph.selectCellForEvent.bind(graph);
graph.selectCellForEvent = (cell: Cell, evt: MouseEvent) => {
    if (evt?.button === 2) return;
    _selectCellForEvent(cell, evt);
};

// Verrouillage de la cellule d'arrière-plan
const _isSelectable = graph.isCellSelectable.bind(graph);
graph.isCellSelectable = (cell) => !isBackgroundCell(cell) && _isSelectable(cell);
const _isMovable = graph.isCellMovable.bind(graph);
graph.isCellMovable = (cell) => !isBackgroundCell(cell) && _isMovable(cell);

// Sélection rectangle (multi-sélection) par-dessus le fond de carte.
// RubberBandHandler ne démarre normalement que si aucune cellule n'est sous
// le curseur (`!me.getState()`) — or le fond (isBackground) EST une cellule,
// et SelectionHandler.mouseDown consomme le mousedown dès qu'une cellule
// existe (branche isMoveEnabled), même non déplaçable. Résultat : dès qu'un
// fond d'écran est présent, un clic-gauche sur la carte n'atteint jamais
// RubberBandHandler et la sélection rectangle ne peut pas démarrer.
// On force donc le rubberband via isForceRubberbandEvent (comme le fait
// déjà MaxGraph pour Alt), qui se déclenche en amont de SelectionHandler et
// PanningHandler et consomme l'événement avant eux. On exclut le clic
// droit pour laisser le correctif bgPanning (ci-dessous) gérer seul le
// panoramique sur le fond, sans double appel concurrent.
const rubberBandHandler = graph.getPlugin<RubberBandHandler>('RubberBandHandler');
if (rubberBandHandler) {
    const _isForceRubberbandEvent = rubberBandHandler.isForceRubberbandEvent.bind(rubberBandHandler);
    rubberBandHandler.isForceRubberbandEvent = (me: InternalMouseEvent) => {
        if (_isForceRubberbandEvent(me)) return true;
        return me.getEvent()?.button !== 2 && isBackgroundCell(me.getCell());
    };
}

// La taille d'un groupe ne doit jamais être modifiée manuellement (elle est
// calculée à la création et doit rester cohérente avec son contenu). Un
// border reste resizable même avec des enfants : le listener CELLS_RESIZED
// (plus bas) le ré-agrandit ensuite si besoin pour continuer à les contenir.
const _isResizable = graph.isCellResizable.bind(graph);
graph.isCellResizable = (cell) =>
    (isRectangleCell(cell) || (cell?.children?.length ?? 0) === 0) && _isResizable(cell);

// Borders imbriqués : le parent grandit pour contenir un enfant redimensionné
// (cf. listener CELLS_RESIZED plus bas), qui doit donc pouvoir dépasser
// librement les bords de son parent — on désactive l'extension native
// (sans padding, sans gestion du débordement haut/gauche) pour ne garder que
// notre propre logique comme unique source de vérité.
graph.setExtendParents(false);

// Un enfant de border ne doit pas être bridé aux bords de son parent : c'est
// le parent qui s'adapte (n'affecte pas les icônes ni les groupes du bouton
// Group, dont le parent n'est pas un border).
const _isConstrainChild = graph.isConstrainChild.bind(graph);
graph.isConstrainChild = (cell) => {
    if (isRectangleCell(cell?.getParent())) return false;
    return _isConstrainChild(cell);
};

// Sélection des sommets
VertexHandlerConfig.selectionColor = '#00a8ff';
VertexHandlerConfig.selectionStrokeWidth = 2;

// Resize uniquement par les coins (largeur et hauteur modifiées ensemble) :
// on masque les points d'accroche N/S/E/W, ne gardant que NW/NE/SW/SE
// (indices 0, 2, 5 et 7 dans l'ordre de création des sizers). isSizerVisible
// est appelée depuis le constructeur de VertexHandler : la surcharger sur
// l'instance après coup est trop tard, il faut sous-classer.
const RESIZE_CORNER_INDICES = new Set([0, 2, 5, 7]);

class CornerOnlyVertexHandler extends VertexHandler {
    isSizerVisible(index: number): boolean {
        return RESIZE_CORNER_INDICES.has(index);
    }

    // Le mouseDown natif de MaxGraph teste `if (handle)` : l'indice 0 (coin
    // haut-gauche, NW) est falsy, donc le resize par ce coin ne démarrait jamais
    // et le clic retombait sur SelectionHandler, qui déplaçait la cellule.
    mouseDown(_sender: unknown, me: InternalMouseEvent): void {
        if (!me.isConsumed() && this.graph.isEnabled()) {
            const handle = this.getHandleForEvent(me);
            if (handle !== null) {
                this.start(me.getGraphX(), me.getGraphY(), handle);
                me.consume();
            }
        }
    }

    // Le resize doit toujours conserver le ratio largeur/hauteur, pas
    // seulement quand Shift est maintenu — sauf pour les rectangles et les
    // textes, qui doivent pouvoir être étirés librement dans chaque direction.
    isConstrainedEvent(me: InternalMouseEvent): boolean {
        const cell = this.state.cell;
        if (isRectangleCell(cell) || isTextCell(cell)) {
            return super.isConstrainedEvent(me);
        }
        return true;
    }
}

graph.createVertexHandler = (state) => new CornerOnlyVertexHandler(state);

//-----------------------------------------------------------------------
// Undo / Redo

const undoManager = new UndoManager();
let physicsSuppressUndo = false;
let isDirty = false;

const undoListener = (_sender: unknown, evt: EventObject) => {
    if (physicsSuppressUndo) return;
    const edit = evt.getProperty('edit') as UndoableEdit | undefined;
    if (edit) {
        undoManager.undoableEditHappened(edit);
        isDirty = true;
    }
};

model.addListener(InternalEvent.UNDO, undoListener);
graph.getView().addListener(InternalEvent.UNDO, undoListener);

const undoButton = document.getElementById('undoButton') as HTMLButtonElement | null;
const redoButton = document.getElementById('redoButton') as HTMLButtonElement | null;

if (undoButton) {
    undoButton.addEventListener('click', () => {
        if (undoManager.canUndo()) undoManager.undo();
    });
}

if (redoButton) {
    redoButton.addEventListener('click', () => {
        if (undoManager.canRedo()) undoManager.redo();
    });
}

document.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.ctrlKey && event.key === 'z') {
        event.preventDefault();
        if (undoManager.canUndo()) undoManager.undo();
    } else if (event.ctrlKey && event.key === 'y') {
        event.preventDefault();
        if (undoManager.canRedo()) undoManager.redo();
    }
});

// Echap ne doit avoir d'effet que dans l'éditeur (annuler l'édition d'une
// cellule, fermer un menu contextuel, etc.) : on empêche sa remontée vers le
// reste de la page (fermeture d'une modale parente, navigation, ...).
document.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.key !== 'Escape') return;
    event.preventDefault();
    event.stopPropagation();
});

// --------------------------------------------------------------------------------
// Menus contextuels

const MENU_OFFSET_X = 75;
const MENU_OFFSET_Y = 100;

const edgeContextMenu = document.getElementById('edge-context-menu') as HTMLDivElement | null;
const edgeColorSelect = document.getElementById('edge-color-select') as HTMLInputElement | null;
const shapeFillSelect = document.getElementById('edge-fill-select') as HTMLInputElement | null;
const shapeTextColorSelect = document.getElementById('edge-text-color-select') as HTMLInputElement | null;
const shapeFillGroup = document.getElementById('edge-fill-group') as HTMLDivElement | null;
const shapeTextGroup = document.getElementById('edge-text-group') as HTMLDivElement | null;
const thicknessSelect = document.getElementById('edge-thickness-select') as HTMLSelectElement | null;
const dashSelect = document.getElementById('edge-dash-select') as HTMLSelectElement | null;
const routingSelect = document.getElementById('edge-routing-select') as HTMLSelectElement | null;

const textContextMenu = document.getElementById('text-context-menu') as HTMLDivElement | null;
const textFontSelect = document.getElementById('text-font-select') as HTMLSelectElement | null;
const textColorSelect = document.getElementById('text-color-select') as HTMLInputElement | null;
const textSizeSelect = document.getElementById('text-size-select') as HTMLSelectElement | null;
const textBoldSelect = document.getElementById('text-bold-select') as HTMLButtonElement | null;
const textItalicSelect = document.getElementById('text-italic-select') as HTMLButtonElement | null;
const textUnderlineSelect = document.getElementById('text-underline-select') as HTMLButtonElement | null;

let selectedCell: Cell | null = null;
let selectedEdgeCells: Cell[] = [];
// true quand le menu est ouvert sur un rectangle (3 couleurs), false sur un lien.
let shapeMenuOpen = false;

function hideContextMenus(): void {
    if (textContextMenu) textContextMenu.style.display = 'none';
    if (edgeContextMenu) edgeContextMenu.style.display = 'none';
}

// <input type="color"> n'accepte que #rrggbb : normalise #rgb et remplace
// toute autre valeur de style ('none', nom de couleur...) par `fallback`.
function toHexColor(value: string | undefined | null, fallback: string): string {
    if (!value) return fallback;
    if (/^#[0-9a-f]{6}$/i.test(value)) return value.toLowerCase();
    const short = /^#([0-9a-f])([0-9a-f])([0-9a-f])$/i.exec(value);
    if (short) return `#${short[1]}${short[1]}${short[2]}${short[2]}${short[3]}${short[3]}`.toLowerCase();
    return fallback;
}

function showEdgeMenu(x: number, y: number, style: CellStateStyle, shape = false): void {
    if (!edgeContextMenu || !textContextMenu) return;
    shapeMenuOpen = shape;
    edgeContextMenu.style.display = 'flex';
    edgeContextMenu.style.left = `${x + MENU_OFFSET_X}px`;
    edgeContextMenu.style.top = `${y + MENU_OFFSET_Y}px`;
    if (shapeFillGroup) shapeFillGroup.style.display = shape ? 'flex' : 'none';
    if (shapeTextGroup) shapeTextGroup.style.display = shape ? 'flex' : 'none';
    if (shapeFillSelect) shapeFillSelect.value = toHexColor(style.fillColor, '#ffffff');
    if (shapeTextColorSelect) shapeTextColorSelect.value = toHexColor(style.fontColor, '#000000');
    if (edgeColorSelect) edgeColorSelect.value = toHexColor(style.strokeColor, '#000000');
    if (thicknessSelect) thicknessSelect.value = String(style.strokeWidth ?? '1');
    if (dashSelect) {
        const dashed = (style as any).dashed;
        const dashPattern = (style as any).dashPattern as string | undefined;
        if (!dashed) {
            dashSelect.value = 'solid';
        } else if (dashPattern === '2 6') {
            dashSelect.value = 'dotted';
        } else if (dashPattern === '8 3 2 3') {
            dashSelect.value = 'dash-dot';
        } else {
            dashSelect.value = 'dashed';
        }
    }
    if (routingSelect) {
        const es = style.edgeStyle;
        if (style.curved && (es === 'straightEdgeStyle' || !es)) {
            routingSelect.value = 'arc';
        } else if (es === 'orthogonalEdgeStyle' || es === 'elbowEdgeStyle') {
            routingSelect.value = 'orthogonal';
        } else {
            routingSelect.value = 'straight';
        }
    }
    textContextMenu.style.display = 'none';
}

function showTextMenu(x: number, y: number, style: CellStateStyle, cellStyle: AppCellStyle | undefined): void {
    if (!edgeContextMenu || !textContextMenu) return;
    textContextMenu.style.display = 'flex';
    textContextMenu.style.left = `${x + MENU_OFFSET_X}px`;
    textContextMenu.style.top = `${y + MENU_OFFSET_Y}px`;
    if (textColorSelect) textColorSelect.value = style.fontColor ?? '#000000';
    if (textFontSelect) textFontSelect.value = style.fontFamily ?? 'Arial';
    if (textSizeSelect) textSizeSelect.value = String(style.fontSize ?? '12');
    edgeContextMenu.style.display = 'none';

    const fontStyle = cellStyle?.fontStyle ?? 0;
    textBoldSelect?.classList.toggle('selected', !!(fontStyle & 1));
    textItalicSelect?.classList.toggle('selected', !!(fontStyle & 2));
    textUnderlineSelect?.classList.toggle('selected', !!(fontStyle & 4));
}

// Sommet dont on peut modifier le trait/la couleur : un rectangle (même s'il
// contient des objets ou porte un texte — c'est alors le menu rectangle qui
// prime sur le menu texte, dont la couleur de texte est reprise) ou une forme
// sans texte ni image ni enfant. Les groupes (invisibles, avec enfants) en
// sont exclus.
function isShapeStyleTarget(cell: Cell): boolean {
    if (!cell.isVertex()) return false;
    if (isRectangleCell(cell)) return true;
    const cellValue = cell.value as string | null;
    const hasText = !!cellValue && cellValue.trim() !== '';
    if (hasText || styleOf(cell)?.image) return false;
    return !cell.children || cell.children.length === 0;
}

function isEdgeStyleTarget(cell: Cell): boolean {
    return cell.isEdge() || isShapeStyleTarget(cell);
}

function distanceToSegment(px: number, py: number, ax: number, ay: number, bx: number, by: number): number {
    const dx = bx - ax, dy = by - ay;
    const lenSq = dx * dx + dy * dy;
    let t = lenSq > 0 ? ((px - ax) * dx + (py - ay) * dy) / lenSq : 0;
    t = Math.max(0, Math.min(1, t));
    return Math.hypot(px - (ax + t * dx), py - (ay + t * dy));
}

// graph.getCellAt() teste la distance au tracé avec une tolérance de clic
// fixe (4px, graph.tolerance), quelle que soit l'épaisseur du trait — un
// lien épais est pourtant visuellement bien plus large que ça. De plus, pour
// un arc (style.curved), il teste la ligne brisée source → point d'inflexion
// → cible (state.absolutePoints), pas la courbe réellement affichée :
// paintCurvedLine() dessine une quadratique dont le point n'est qu'un point
// de contrôle — la courbe ne passe jamais par lui et s'en écarte de moitié à
// son sommet. On complète ici en cherchant, pour tout type d'edge, le tracé
// réellement affiché (segments pour droit/angles droits, courbe de Bézier
// pour un arc) le plus proche du point cliqué, avec une tolérance qui suit
// l'épaisseur du trait.
function findEdgeAt(x: number, y: number): Cell | null {
    const baseTolerance = 8;
    const scale = graph.getView().scale || 1;
    let closest: Cell | null = null;
    let closestDist = Infinity;

    for (const edge of graph.getChildEdges(graph.getDefaultParent())) {
        const pts = graph.getView().getState(edge)?.absolutePoints;
        if (!pts || pts.length < 2) continue;

        // La tolérance suit l'épaisseur du trait (en unités modèle, converties
        // à l'échelle de vue courante) : un trait épais est visuellement plus
        // large, le clic doit pouvoir toucher n'importe quel point du tracé.
        const strokeWidth = edge.style?.strokeWidth ?? 1;
        const tolerance = baseTolerance + (strokeWidth * scale) / 2;

        if (edge.style?.curved && pts.length === 3) {
            const [p0, p1, p2] = pts;
            if (!p0 || !p1 || !p2) continue;
            for (let t = 0; t <= 1; t += 0.05) {
                const mt = 1 - t;
                const bx = mt * mt * p0.x + 2 * mt * t * p1.x + t * t * p2.x;
                const by = mt * mt * p0.y + 2 * mt * t * p1.y + t * t * p2.y;
                const dist = Math.hypot(bx - x, by - y);
                if (dist <= tolerance && dist < closestDist) {
                    closestDist = dist;
                    closest = edge;
                }
            }
        } else {
            for (let i = 1; i < pts.length; i++) {
                const a = pts[i - 1], b = pts[i];
                if (!a || !b) continue;
                const dist = distanceToSegment(x, y, a.x, a.y, b.x, b.y);
                if (dist <= tolerance && dist < closestDist) {
                    closestDist = dist;
                    closest = edge;
                }
            }
        }
    }
    return closest;
}

graph.container.addEventListener('contextmenu', (event: MouseEvent) => {
    event.preventDefault();

    const cell = (graph.getCellAt(event.offsetX, event.offsetY) as Cell | null)
        ?? findEdgeAt(event.offsetX, event.offsetY);
    if (!cell) return;

    if (isBackgroundCell(cell)) {
        hideContextMenus();
        return;
    }

    const rect = container.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const currentStyle = graph.getCellStyle(cell);

    if (cell.isEdge()) {
        selectedCell = cell;
        const allSelected = graph.getSelectionCells() as Cell[];
        const selectedIds = new Set(allSelected.map((c) => String(c.id)));
        selectedEdgeCells = selectedIds.has(String(cell.id)) && allSelected.length > 1
            ? allSelected.filter((c) => isEdgeStyleTarget(c))
            : [cell];
        showEdgeMenu(x, y, currentStyle);
    } else if (cell.isVertex()) {
        const cellValue = cell.value as string | null;
        const hasText = !!cellValue && cellValue.trim() !== '';
        const cellStyle = styleOf(cell);

        // Le rectangle est testé en premier : son libellé ne doit pas
        // faire basculer sur le menu de modification de texte.
        if (!isRectangleCell(cell) && hasText && textColorSelect && textFontSelect && textSizeSelect) {
            selectedCell = cell;
            selectedEdgeCells = [];
            showTextMenu(x, y, currentStyle, cellStyle);
        } else if (isShapeStyleTarget(cell)) {
            selectedCell = cell;
            const allSelected = graph.getSelectionCells() as Cell[];
            const selectedIds = new Set(allSelected.map((c) => String(c.id)));
            selectedEdgeCells = selectedIds.has(String(cell.id)) && allSelected.length > 1
                ? allSelected.filter((c) => isEdgeStyleTarget(c))
                : [cell];
            showEdgeMenu(x, y, currentStyle, true);
        } else {
            hideContextMenus();
        }
    } else {
        hideContextMenus();
    }
});

document.getElementById('apply-edge-style')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!selectedCell || !edgeColorSelect || !thicknessSelect) return;

    const cells = selectedEdgeCells.length > 0 ? selectedEdgeCells : [selectedCell];
    const thickness = parseInt(thicknessSelect.value, 10) || 1;
    const dashValue = dashSelect?.value ?? 'solid';

    graph.batchUpdate(() => {
        for (const cell of cells) {
            const style: CellStateStyle = { ...(cell.style ?? {}) };

            style.strokeColor = edgeColorSelect!.value;
            // Fond et texte ne concernent que les rectangles, et seulement
            // quand le menu a été ouvert dessus (sinon ces champs sont masqués).
            if (shapeMenuOpen && !cell.isEdge()) {
                if (shapeFillSelect) style.fillColor = shapeFillSelect.value;
                if (shapeTextColorSelect) style.fontColor = shapeTextColorSelect.value;
            }
            style.strokeWidth = thickness;

            const s = style as any;
            if (dashValue === 'solid') {
                s.dashed = false;
                delete s.dashPattern;
                delete s.fixDash;
            } else if (dashValue === 'dotted') {
                s.dashed = true;
                s.dashPattern = '2 6';
                s.fixDash = true;
            } else if (dashValue === 'dash-dot') {
                s.dashed = true;
                s.dashPattern = '8 3 2 3';
                s.fixDash = true;
            } else {
                // dashed
                s.dashed = true;
                delete s.dashPattern;
                s.fixDash = true;
            }

            const routing = routingSelect?.value;
            style.edgeStyle = routing === 'orthogonal' ? 'orthogonalEdgeStyle' : 'straightEdgeStyle';

            if (routing === 'arc') {
                style.curved = true;
                // Ne pose le point d'inflexion par défaut, au milieu de l'edge,
                // que si aucun point n'existe déjà — pour ne jamais déplacer un
                // arc ou un angle déjà positionné (manuellement ou en arc).
                const geo = cell.getGeometry()?.clone();
                const s = cell.source, t = cell.target;
                if (geo && (!geo.points || geo.points.length === 0) && s && t) {
                    const cs = modelCenter(s), ct = modelCenter(t);
                    const mx = (cs.x + ct.x) / 2, my = (cs.y + ct.y) / 2;
                    const dx = ct.x - cs.x, dy = ct.y - cs.y;
                    const len = Math.hypot(dx, dy) || 1;
                    const nx = -dy / len, ny = dx / len;
                    const offset = 30;
                    geo.points = [new Point(mx + nx * offset, my + ny * offset)];
                    cell.setGeometry(geo);
                }
            } else {
                // On ne retire le point que s'il provenait du mode arc :
                // un angle posé manuellement en mode droit/orthogonal n'est
                // jamais effacé par un simple changement de routage.
                if (style.curved) {
                    const geo = cell.getGeometry()?.clone();
                    if (geo) {
                        geo.points = null;
                        cell.setGeometry(geo);
                    }
                }
                style.curved = false;
            }

            model.setStyle(cell, style);
            graph.refresh(cell);
        }
    });

    if (edgeContextMenu) edgeContextMenu.style.display = 'none';
});

document.getElementById('apply-text-style')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!selectedCell || !textFontSelect || !textColorSelect || !textSizeSelect) return;

    graph.batchUpdate(() => {
        const style: CellStateStyle = { ...(selectedCell!.style ?? {}) };

        style.fontFamily = textFontSelect!.value;
        style.fontColor = textColorSelect!.value;
        style.fontSize = parseInt(textSizeSelect!.value, 10) || 12;

        let flag = 0;
        if (textBoldSelect?.classList.contains('selected')) flag |= 1;
        if (textItalicSelect?.classList.contains('selected')) flag |= 2;
        if (textUnderlineSelect?.classList.contains('selected')) flag |= 4;
        style.fontStyle = flag;

        model.setStyle(selectedCell!, style);

        // Pour un champ texte libre, la taille de la cellule doit refléter
        // celle du texte (police/taille pouvant changer ses dimensions) —
        // pas pour un sommet-icône, dont la taille de l'icône est fixe.
        if (isTextCell(selectedCell)) {
            graph.updateCellSize(selectedCell!, true);
        }

        graph.refresh(selectedCell!);
    });

    if (textContextMenu) textContextMenu.style.display = 'none';
});

// Boutons avec classe .button → toggle "selected"
document
    .querySelectorAll<HTMLButtonElement>('.button')
    .forEach((button) => {
        button.addEventListener('click', () => button.classList.toggle('selected'));
    });

// Cacher les menus contextuels en cliquant ailleurs
document.addEventListener('click', (event) => {
    const target = event.target as globalThis.Node | null;
    if (textContextMenu && !textContextMenu.contains(target)) textContextMenu.style.display = 'none';
    if (edgeContextMenu && !edgeContextMenu.contains(target)) edgeContextMenu.style.display = 'none';
});

// --------------------------------------------------------------------------------
// Grille

graph.setGridEnabled(false);

// Overlay positionné au-dessus du SVG MaxGraph (qui contient l'image de fond)
// pour que la grille soit visible par-dessus l'image de fond.
// pointer-events: none pour ne pas intercepter les clics/drags du graphe.
const gridOverlay = document.createElement('div');
gridOverlay.style.cssText =
    'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:10;';
container.appendChild(gridOverlay);

function updateGridBackground(): void {
    if (!graph.isGridEnabled()) return;
    const s = graph.view.scale;
    const t = graph.view.translate;
    const cellSize = graph.getGridSize() * s;
    const ox = ((t.x * s) % cellSize + cellSize) % cellSize;
    const oy = ((t.y * s) % cellSize + cellSize) % cellSize;
    gridOverlay.style.backgroundSize = `${cellSize}px ${cellSize}px`;
    gridOverlay.style.backgroundPosition = `${ox}px ${oy}px`;
}

function setGridVisible(active: boolean): void {
    graph.setGridEnabled(active);
    if (active) {
        gridOverlay.style.backgroundImage =
            'radial-gradient(circle, rgba(0,0,0,0.25) 1px, transparent 1px)';
        updateGridBackground();
    } else {
        gridOverlay.style.backgroundImage = 'none';
    }
    const btn = document.getElementById('grid-btn');
    if (btn) btn.setAttribute('aria-pressed', String(active));
}

graph.view.addListener(InternalEvent.SCALE, () => updateGridBackground());
graph.view.addListener(InternalEvent.TRANSLATE, () => updateGridBackground());
graph.view.addListener(InternalEvent.SCALE_AND_TRANSLATE, () => updateGridBackground());

document.getElementById('grid-btn')?.addEventListener('click', () => {
    setGridVisible(!graph.isGridEnabled());
});

// -----------------------------------------------------------------------
// Panning

graph.setPanning(true);
graph.allowAutoPanning = true;
graph.useScrollbarsForPanning = true;

// PanningHandler ne s'active pas quand une cellule est sous la souris.
// La cellule d'arrière-plan (isBackground) est une cellule MaxGraph, donc le
// clic-droit dessus ne déclenche pas le panning natif.
//
// On écoute en phase CAPTURE sur window pour que MaxGraph ne puisse pas
// bloquer les événements via stopPropagation. Les coordonnées sont corrigées
// du décalage panDx/panDy (offset CSS du canvas SVG) avant d'appeler getCellAt.
let bgPanning = false;
let bgPanStartClientX = 0;
let bgPanStartClientY = 0;
let bgPanStartDx = 0;
let bgPanStartDy = 0;

window.addEventListener('pointerdown', (e: PointerEvent) => {
    if (e.button !== 2) return;
    if (!container.contains(e.target as Node)) return;

    const rect = container.getBoundingClientRect();
    const cell = graph.getCellAt(
        e.clientX - rect.left - graph.panDx,
        e.clientY - rect.top  - graph.panDy,
    ) as Cell | null;
    if (!isBackgroundCell(cell)) return;

    bgPanning = true;
    bgPanStartClientX = e.clientX;
    bgPanStartClientY = e.clientY;
    bgPanStartDx = graph.panDx;
    bgPanStartDy = graph.panDy;
    // Empêche MaxGraph's PanningHandler de s'activer en parallèle
    // (sinon double appel panGraph → saut parasite au début).
    e.stopPropagation();
}, true);

window.addEventListener('pointermove', (e: PointerEvent) => {
    if (!bgPanning) return;
    graph.panGraph(
        bgPanStartDx + e.clientX - bgPanStartClientX,
        bgPanStartDy + e.clientY - bgPanStartClientY,
    );
}, true);

window.addEventListener('pointerup', () => {
    if (!bgPanning) return;
    // Convertit le décalage CSS (panDx/panDy) en view.translate logique,
    // exactement comme le fait PanningHandler sur son mouseup.
    // Sans ça, le référentiel de coordonnées de MaxGraph diverge de l'affichage
    // et les déplacements de cellules suivants sautent.
    const view  = graph.getView();
    const scale = view.scale;
    view.setTranslate(
        view.translate.x + graph.panDx / scale,
        view.translate.y + graph.panDy / scale,
    );
    graph.panGraph(0, 0);
    bgPanning = false;
}, true);
window.addEventListener('blur', () => {
    if (!bgPanning) return;
    const view  = graph.getView();
    const scale = view.scale;
    view.setTranslate(
        view.translate.x + graph.panDx / scale,
        view.translate.y + graph.panDy / scale,
    );
    graph.panGraph(0, 0);
    bgPanning = false;
});

//-------------------------------------------------------------------------
// LOAD / SAVE

export function loadGraph(xml: string) {
    new ModelXmlSerializer(model).import(xml);
    refreshParallelEdges();
    // Cadre la totalité des objets dans l'écran, aligné à gauche
    graph.getPlugin<FitPlugin>('fit')?.fit({margin: 20});
    isDirty = false; // le chargement initial n'est pas une modification utilisateur
}

(window as any).loadGraph = loadGraph;

async function saveGraphToDatabase(
    id: number | string,
    name: string,
    type: string,
    content: string,
): Promise<void> {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
    const csrfToken = csrfMeta?.content;
    if (!csrfToken) {
        console.error('CSRF token manquant');
        alert('Token CSRF manquant.');
        return;
    }

    try {
        const response = await fetch(`/admin/graphs/${id}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({_method: 'PUT', id, name, type, content}),
        });

        if (response.status !== 200) {
            let errorMessage = 'Erreur lors de la sauvegarde du graphe.';
            try {
                const error = await response.json();
                if (error?.message) errorMessage = error.message;
            } catch { /* ignore */
            }
            throw new Error(errorMessage);
        }
        isDirty = false;
    } catch (error) {
        console.error('Erreur lors de la sauvegarde :', error);
        alert('Erreur lors de la sauvegarde du graphe.');
    }
}

function getXMLGraph(): string {
    return new ModelXmlSerializer(graph.getDataModel()).export();
}

(window as any).getXMLGraph = getXMLGraph;

function saveGraph() {
    const idInput = document.querySelector('#id') as HTMLInputElement | null;
    const nameInput = document.querySelector('#name') as HTMLInputElement | null;
    const typeInput = document.querySelector('#type') as HTMLInputElement | null;

    if (!idInput || !nameInput || !typeInput) {
        alert('Champs id / name / type manquants');
        return;
    }

    saveGraphToDatabase(idInput.value, nameInput.value, typeInput.value, new ModelXmlSerializer(model).export());
}

document.getElementById('saveButton')?.addEventListener('click', saveGraph);

// Les modifications sur le nom ou le type marquent aussi le graphe comme modifié
document.getElementById('name')?.addEventListener('input', () => { isDirty = true; });
document.getElementById('type')?.addEventListener('change', () => { isDirty = true; });

// Sauvegarde via le bouton formulaire (PUT) : vider isDirty avant la
// navigation pour que beforeunload ne bloque pas la redirection.
document.getElementById('btn-save')?.addEventListener('click', () => { isDirty = false; }, true);

// Confirmation avant fermeture de l'onglet / navigation externe
window.addEventListener('beforeunload', (e) => {
    if (!isDirty) return;
    e.preventDefault();
    e.returnValue = '';
});

// Confirmation avant le lien "Retour à la liste"
document.getElementById('btn-cancel')?.addEventListener('click', (e) => {
    if (isDirty && !confirm('Des modifications non sauvegardées seront perdues. Quitter quand même ?')) {
        e.preventDefault();
    }
});

//-------------------------------------------------------------------------
// Utilitaires

type Pt = { x: number; y: number };

function getGraphPointFromEvent(graph: Graph, evt: MouseEvent | DragEvent): Pt {
    const pt = graph.getPointForEvent(evt);
    return {x: pt.x, y: pt.y};
}

function getFilter(): string[] {
    const select = document.getElementById('filters') as HTMLSelectElement | null;
    if (!select) return [];
    return Array.from(select.options).filter(o => o.selected).map(o => o.value);
}

function getAttrFilter(): string[] {
    const select = document.getElementById('attr-filter') as HTMLSelectElement | null;
    if (!select) return [];
    return Array.from(select.options).filter(o => o.selected).map(o => o.value);
}

function matchesAttrFilter(node: MapNode, attrFilter: string[]): boolean {
    if (attrFilter.length === 0) return true;
    if (!node.attributes) return false;
    const nodeAttrs = node.attributes.split(' ').map(s => s.trim()).filter(Boolean);
    return attrFilter.some(a => nodeAttrs.includes(a));
}

// 1 = amont (up), 2 = aval (down), 3 = les deux
function getDirection(): number {
    if ((document.getElementById('direction-up') as HTMLInputElement | null)?.checked) return 1;
    if ((document.getElementById('direction-down') as HTMLInputElement | null)?.checked) return 2;
    return 3;
}

function matchesDirection(direction: number, source: MapNode, target: MapNode): boolean {
    if (direction === 1 && source.order <= target.order) return false;
    if (direction === 2 && source.order >= target.order) return false;
    return true;
}

//-------------------------------------------------------------------------
// Affichage IP / tags dans les labels

function getShowIP(): boolean {
    return document.getElementById('toggleIP')?.classList.contains('active') ?? false;
}

function getShowAttr(): boolean {
    return document.getElementById('toggleAttr')?.classList.contains('active') ?? false;
}

function buildLabel(node: MapNode): string {
    if (getShowIP() && node.title) return node.label + '\n' + node.title;
    if (getShowAttr() && node.attributes) return node.label + '\n' + node.attributes;
    return node.label;
}

function refreshNodeLabels(): void {
    graph.batchUpdate(() => {
        for (const cell of collectVertices()) {
            const style = styleOf(cell);
            if (!style?.image || style.isBackground) continue;
            const node = _nodes.get(cell.id as string);
            if (!node) continue;
            cell.value = buildLabel(node);
        }
    });
    graph.refresh();
}

// Désactivés par défaut à chaque chargement de la page (pas de persistance).
function setToggleActive(btn: HTMLElement | null, active: boolean): void {
    if (!btn) return;
    btn.classList.toggle('active', active);
    btn.setAttribute('aria-pressed', String(active));
}

setToggleActive(document.getElementById('toggleIP'), false);
setToggleActive(document.getElementById('toggleAttr'), false);

document.getElementById('toggleIP')?.addEventListener('click', (e) => {
    const btn = e.currentTarget as HTMLElement;
    const isActive = !btn.classList.contains('active');
    setToggleActive(btn, isActive);
    if (isActive) setToggleActive(document.getElementById('toggleAttr'), false);
    refreshNodeLabels();
});

document.getElementById('toggleAttr')?.addEventListener('click', (e) => {
    const btn = e.currentTarget as HTMLElement;
    const isActive = !btn.classList.contains('active');
    setToggleActive(btn, isActive);
    if (isActive) setToggleActive(document.getElementById('toggleIP'), false);
    refreshNodeLabels();
});

// Nombre de liens déjà tracés entre deux sommets (quel que soit leur sens)
function countEdgesBetween(src: Cell, dest: Cell): number {
    return graph.getEdges(src).filter((e) => e.source === dest || e.target === dest).length;
}

// Ajoute tous les liens manquants entre objets déjà présents sur le canevas,
// d'après les données de _nodes (pas seulement ceux du nœud double-cliqué).
function completeMissingEdgesAmongPlacedNodes(parent: Cell): void {
    const placedIds = new Set(collectVertices().filter(isMovableObject).map((v) => String(v.id)));

    for (const id of placedIds) {
        const sourceNode = _nodes.get(id);
        const sourceCell = model.getCell(id) as Cell | null;
        if (!sourceNode || !sourceCell) continue;

        const edgesByTarget = new Map<string, Edge[]>();
        for (const edge of sourceNode.edges) {
            if (!placedIds.has(edge.attachedNodeId)) continue;
            const list = edgesByTarget.get(edge.attachedNodeId);
            if (list) list.push(edge); else edgesByTarget.set(edge.attachedNodeId, [edge]);
        }

        for (const [targetId, edges] of edgesByTarget) {
            const targetCell = model.getCell(targetId) as Cell | null;
            if (!targetCell) continue;
            const alreadyDrawn = countEdgesBetween(sourceCell, targetCell);
            for (const edge of edges.slice(alreadyDrawn)) {
                graph.insertEdge({
                    parent,
                    value: edge.name,
                    source: sourceCell,
                    target: targetCell,
                    style: buildEdgeStyle(edge),
                });
            }
        }
    }
}

// Restaure uniquement les arêtes manquantes connectées au nœud donné.
// Seules les entrées de node.edges sont examinées — aucun autre nœud du graphe
// n'est parcouru.
function restoreMissingEdgesForNode(nodeId: string, parent: Cell): void {
    const node = _nodes.get(nodeId);
    const nodeCell = model.getCell(nodeId) as Cell | null;
    if (!node || !nodeCell) return;

    const edgesByPeer = new Map<string, Edge[]>();
    for (const edge of node.edges) {
        const peerId = edge.attachedNodeId;
        // Le pair doit être un nœud domaine présent dans le graphe.
        const peerCell = model.getCell(peerId) as Cell | null;
        if (!peerCell?.isVertex() || !_nodes.has(peerId)) continue;
        const list = edgesByPeer.get(peerId);
        if (list) list.push(edge); else edgesByPeer.set(peerId, [edge]);
    }

    for (const [peerId, edges] of edgesByPeer) {
        const peerCell = model.getCell(peerId) as Cell | null;
        if (!peerCell) continue;
        const alreadyDrawn = countEdgesBetween(nodeCell, peerCell);
        for (const edge of edges.slice(alreadyDrawn)) {
            graph.insertEdge({
                parent,
                value: edge.name,
                source: nodeCell,
                target: peerCell,
                style: buildEdgeStyle(edge),
            });
        }
    }
}

function modelCenter(cell: Cell): Point {
    const g = cell.getGeometry();
    const w = g?.width ?? 0, h = g?.height ?? 0;
    let x = 0, y = 0;
    let c: Cell | null = cell;
    const root = graph.getDefaultParent();
    while (c && c !== root) {
        const cg = c.getGeometry();
        if (cg && !c.isEdge()) {
            x += cg.x;
            y += cg.y;
        }
        c = c.getParent();
    }
    return new Point(x + w / 2, y + h / 2);
}

// --------------------------------------------------------------------------------
// Borders imbriqués : ré-parenté + croissance du parent

const BORDER_PADDING = 20;

// Coin haut-gauche absolu du repère propre d'une cellule (somme des géométries
// de la cellule jusqu'à la racine, arêtes exclues). Même parcours que
// modelCenter, sans le +w/2,+h/2.
function absTopLeft(cell: Cell | null): Point {
    let x = 0, y = 0;
    const root = graph.getDefaultParent();
    let c: Cell | null = cell;
    while (c && c !== root) {
        const g = c.getGeometry();
        if (g && !c.isEdge()) { x += g.x; y += g.y; }
        c = c.getParent();
    }
    return new Point(x, y);
}

function isDescendantOf(cell: Cell, ancestor: Cell): boolean {
    let c: Cell | null = cell;
    while (c) { if (c === ancestor) return true; c = c.getParent(); }
    return false;
}

// Plus petit border (par aire) contenant le point (x,y) en coordonnées MODÈLE
// absolues. Exclut `exclude` et toute sa descendance (un border ne peut pas
// devenir enfant de son propre enfant). `minArea` : n'accepte que les borders
// strictement plus grands (un rectangle déplacé sur un plus petit doit le
// capturer, pas être avalé par lui).
function findContainingBorder(x: number, y: number, exclude: Cell | null, minArea = 0): Cell | null {
    let best: Cell | null = null;
    let bestArea = Infinity;
    const walk = (parent: Cell) => {
        for (const c of parent.children ?? []) {
            if (isRectangleCell(c) && !(exclude && (c === exclude || isDescendantOf(c, exclude)))) {
                const tl = absTopLeft(c);
                const g = c.getGeometry();
                if (g && x >= tl.x && x <= tl.x + g.width && y >= tl.y && y <= tl.y + g.height) {
                    const area = g.width * g.height;
                    if (area > minArea && area < bestArea) { bestArea = area; best = c; }
                }
            }
            if (c.children?.length) walk(c);
        }
    };
    walk(graph.getDefaultParent());
    return best;
}

// Reparente `cell` sous `newParent` en conservant sa position absolue.
// Les rectangles restent derrière leurs frères (comme au drop palette).
function reparentCell(cell: Cell, newParent: Cell): void {
    if (cell.getParent() === newParent) return;
    const childAbs  = absTopLeft(cell);        // avant re-rattachement (chaîne actuelle)
    const parentAbs = absTopLeft(newParent);
    model.add(newParent, cell);                // retire de l'ancien parent + ajoute au nouveau
    const geo = cell.getGeometry()?.clone();
    if (geo) {
        geo.x = childAbs.x - parentAbs.x;
        geo.y = childAbs.y - parentAbs.y;
        model.setGeometry(cell, geo);
    }
    if (isRectangleCell(cell)) graph.orderCells(true, [cell]);
    ensureBackgroundAtBottom();
}

// Agrandit un border (jamais rétréci) pour contenir tous ses enfants + padding.
// Si des enfants débordent en haut/à gauche, on décale l'origine du border et
// on ré-offsette les enfants pour préserver leur position absolue.
function growBorderToFitChildren(border: Cell): void {
    const geo = border.getGeometry()?.clone();
    if (!geo) return;
    let left = 0, top = 0, right = geo.width, bottom = geo.height;
    for (const child of border.children ?? []) {
        if (child.isEdge()) continue;
        const cg = child.getGeometry();
        if (!cg) continue;
        left   = Math.min(left,   cg.x - BORDER_PADDING);
        top    = Math.min(top,    cg.y - BORDER_PADDING);
        right  = Math.max(right,  cg.x + cg.width  + BORDER_PADDING);
        bottom = Math.max(bottom, cg.y + cg.height + BORDER_PADDING);
    }
    const dx = -Math.min(0, left);
    const dy = -Math.min(0, top);
    if (dx === 0 && dy === 0 && right === geo.width && bottom === geo.height) return;

    graph.batchUpdate(() => {
        if (dx !== 0 || dy !== 0) {
            for (const child of border.children ?? []) {
                if (child.isEdge()) continue;
                const cg = child.getGeometry()?.clone();
                if (!cg) continue;
                cg.x += dx; cg.y += dy;
                child.setGeometry(cg);
            }
        }
        geo.x -= dx;                 // conserve la position absolue des enfants
        geo.y -= dy;
        geo.width  = right - left;
        geo.height = bottom - top;
        border.setGeometry(geo);
    });
}

// Propage la croissance vers les borders ancêtres (imbrication profonde).
function growAncestorBorders(cell: Cell): void {
    let p = cell.getParent();
    while (p && isRectangleCell(p)) {
        growBorderToFitChildren(p);
        p = p.getParent();
    }
}

// Un border rétréci (resize utilisateur) libère les enfants qui se retrouvent
// entièrement en dehors de son nouveau rectangle : ils sont ré-attachés au
// parent du border (position absolue conservée) et ne suivront plus ses
// déplacements. Les enfants seulement partiellement recouverts restent
// attachés (le border regrandit ensuite pour les contenir, cf. CELLS_RESIZED).
function releaseChildrenOutsideBorder(border: Cell): void {
    const g = border.getGeometry();
    const newParent = border.getParent();
    if (!g || !newParent) return;

    const outside = (border.children ?? []).filter((child) => {
        if (child.isEdge()) return false;
        const cg = child.getGeometry();
        if (!cg) return false;
        // Géométrie relative au border : le rectangle occupe [0,0]-[w,h].
        return cg.x >= g.width || cg.x + cg.width <= 0 ||
               cg.y >= g.height || cg.y + cg.height <= 0;
    });

    for (const child of outside) reparentCell(child, newParent);
}

// Remet un border (et ses ancêtres border) derrière les objets et les liens de
// leur parent. Un border doit toujours rester en arrière-plan, mais un ordre
// hérité d'une ancienne sauvegarde peut le placer devant des liens : invisible
// tant qu'il ne les recouvre pas, il les cacherait dès qu'on l'agrandit ou le
// déplace sur eux. Les autres borders frères gardent leur ordre relatif.
function keepBorderBehind(border: Cell): void {
    for (let c: Cell | null = border; c && isRectangleCell(c); c = c.getParent()) {
        const parent = c.getParent();
        if (!parent) break;
        const siblings = parent.children ?? [];
        const firstObject = siblings.findIndex((s) => !isRectangleCell(s) && !isBackgroundCell(s));
        if (firstObject !== -1 && siblings.indexOf(c) > firstObject) model.add(parent, c, firstObject);
    }
}

// Un border agrandi (resize utilisateur) capture tout objet qu'il recouvre
// désormais, même partiellement (icône, texte, autre border...). Boucle
// jusqu'à stabilisation : capturer peut faire grandir le border (padding),
// ce qui peut à son tour recouvrir de nouveaux objets. Bornée par sécurité.
function captureOverlappingObjects(border: Cell): void {
    for (let i = 0; i < 20; i++) {
        const tl = absTopLeft(border);
        const g = border.getGeometry();
        if (!g) return;

        let capturedAny = false;
        for (const v of collectVertices()) {
            if (v === border || isBackgroundCell(v)) continue;
            // Ni déjà enfant (évite un ré-ordonnancement inutile), ni ancêtre
            // (empêcherait tout cycle parent/enfant).
            if (isDescendantOf(v, border) || isDescendantOf(border, v)) continue;

            const vg = v.getGeometry();
            if (!vg) continue;
            // Un border plus grand (ou égal) ne peut pas être avalé par un plus
            // petit : il devrait grandir pour le contenir.
            if (isRectangleCell(v) && vg.width * vg.height >= g.width * g.height) continue;
            const vtl = absTopLeft(v);
            const overlap =
                tl.x < vtl.x + vg.width && vtl.x < tl.x + g.width &&
                tl.y < vtl.y + vg.height && vtl.y < tl.y + g.height;
            if (overlap) {
                reparentCell(v, border);
                capturedAny = true;
            }
        }

        growBorderToFitChildren(border); // recouvre complètement, même un objet capturé partiellement
        if (!capturedAny) return;
    }
}

function pairKeyOf(source: Cell, target: Cell): string {
    const a = String(source.id), b = String(target.id);
    return a < b ? `${a}|${b}` : `${b}|${a}`;
}

// Calcule, avant suppression, les paires de sommets dont le nombre de liens
// parallèles va changer — pour ne recalculer ensuite que celles-ci et ne
// jamais déplacer les points d'inflexion des liens non concernés.
function collectAffectedPairs(cells: Cell[]): Set<string> {
    const pairs = new Set<string>();
    for (const cell of cells) {
        if (cell.isEdge()) {
            const s = cell.source, t = cell.target;
            if (s && t) pairs.add(pairKeyOf(s, t));
        } else if (cell.isVertex()) {
            for (const edge of graph.getEdges(cell)) {
                const s = edge.source, t = edge.target;
                if (s && t) pairs.add(pairKeyOf(s, t));
            }
        }
    }
    return pairs;
}

// Exécute une mutation (ajout de nœud(s)/lien(s)) et renvoie les paires de
// sommets pour lesquelles un nouveau lien vient d'apparaître — pour ne
// recalculer ensuite que celles-ci et ne jamais déplacer les points
// d'inflexion des liens déjà présents ailleurs dans le graphe.
function collectNewEdgePairs(parent: Cell, mutate: () => void): Set<string> {
    const before = new Set(graph.getChildEdges(parent).map((e) => String(e.id)));
    mutate();
    const pairs = new Set<string>();
    for (const edge of graph.getChildEdges(parent)) {
        if (before.has(String(edge.id))) continue;
        const s = edge.source, t = edge.target;
        if (s && t) pairs.add(pairKeyOf(s, t));
    }
    return pairs;
}

// Ne recalcule que les paires de sommets dont un lien vient d'être retiré,
// pour ne jamais toucher aux liens des autres paires du graphe.
function refreshParallelEdges(onlyPairs?: Set<string>): void {
    if (onlyPairs && onlyPairs.size === 0) return;

    const parent = graph.getDefaultParent();
    const edges = graph.getChildEdges(parent);

    const groups = new Map<string, Cell[]>();
    for (const edge of edges) {
        const s = edge.source, t = edge.target;
        if (!s || !t) continue;
        const key = pairKeyOf(s, t);
        if (onlyPairs && !onlyPairs.has(key)) continue;
        const list = groups.get(key);
        if (list) list.push(edge); else groups.set(key, [edge]);
    }

    graph.batchUpdate(() => {
        for (const list of groups.values()) {
            const n = list.length;

            // Arête seule entre ces deux sommets (qu'elle l'ait toujours été ou
            // qu'elle vienne de le redevenir après suppression d'un lien
            // parallèle) : on ne touche jamais à sa géométrie ni à son style.
            // Un arc (style.curved) — automatique ou posé volontairement via le
            // menu de routage — ne doit jamais perdre son point d'inflexion
            // pendant l'édition du graphe ; seul un changement explicite de
            // routage (menu contextuel) peut le retirer.
            if (n <= 1) continue;

            // Groupe de liens parallèles : si l'un d'eux a déjà des points définis
            // (angle/arc chargé depuis la sauvegarde ou posé manuellement), on
            // laisse tout le groupe tel quel plutôt que d'écraser un
            // positionnement déjà établi (notamment au chargement du graphe).
            const alreadyPositioned = list.some((edge) => (edge.getGeometry()?.points?.length ?? 0) > 0);
            if (alreadyPositioned) continue;

            list.forEach((edge, i) => {
                const geo = edge.getGeometry()?.clone();
                if (!geo) return;
                const style: CellStateStyle = { ...(edge.style ?? {}) };
                const cs = modelCenter(edge.source!), ct = modelCenter(edge.target!);
                const mx = (cs.x + ct.x) / 2, my = (cs.y + ct.y) / 2;
                const dx = ct.x - cs.x, dy = ct.y - cs.y;
                const len = Math.hypot(dx, dy) || 1;
                const nx = -dy / len, ny = dx / len;
                const offset = (i - (n - 1) / 2) * 22;
                geo.points = [new Point(mx + nx * offset, my + ny * offset)];
                style.curved = true;
                model.setStyle(edge, style);
                edge.setGeometry(geo);
            });
        }
    });
    graph.refresh();
}

function buildEdgeStyle(edge: Edge): CellStateStyle {
    const isFlux = edge.edgeType === 'FLUX' || edge.edgeType === 'LFLUX';
    const isCable = edge.edgeType === 'CABLE';
    const isLink = edge.edgeType === 'LINK';
    return {
        editable: false,
        // Un lien physique (CABLE) garde la couleur définie dans le JSON.
        strokeColor: isCable ? (edge.color ?? '#000000') : '#000000',
        strokeWidth: isCable ? 3 : 2,
        // Comme dans l'explorateur : appartenance (LINK) en pointillé, flux en trait plein.
        dashed: isLink,
        startArrow: isFlux && (edge.bidirectional || edge.edgeDirection === 'FROM') ? 'classic' : 'none',
        endArrow: isFlux && (edge.bidirectional || edge.edgeDirection === 'TO') ? 'classic' : 'none',
    };
}

graph.enterStopsCellEditing = true;

//-------------------------------------------------------------------------
// Drag & drop (gestionnaire unique)

const fontBtn = document.getElementById('font-btn') as HTMLElement | null;
const squareIcon = document.getElementById('square-btn') as HTMLElement | null;
const nodeIcon = document.getElementById('nodeImage') as HTMLImageElement | null;
const nodeSelector = document.getElementById('node') as HTMLSelectElement | null;

fontBtn?.addEventListener('dragstart', (e: DragEvent) => e.dataTransfer?.setData('node-type', 'text-node'));
squareIcon?.addEventListener('dragstart', (e: DragEvent) => e.dataTransfer?.setData('node-type', 'square-node'));
nodeIcon?.addEventListener('dragstart', (e: DragEvent) => e.dataTransfer?.setData('node-type', 'icon-node'));

container.addEventListener('dragover', (event: DragEvent) => event.preventDefault());

container.addEventListener('drop', (event: DragEvent) => {
    event.preventDefault();
    const type = event.dataTransfer?.getData('node-type');
    if (!type) return;

    const pt = getGraphPointFromEvent(graph, event);
    const parent = graph.getDefaultParent();

    if (type === 'text-node') {
        graph.batchUpdate(() => {
            const vertex = graph.insertVertex({
                parent,
                value: 'Text',
                position: [pt.x, pt.y],
                size: [150, 30],
                style: {
                    fillColor: 'none',
                    strokeColor: 'none',
                    fontColor: '#000000',
                    fontSize: 14,
                    align: 'left',
                    // 'top' (plutôt que 'middle') évite tout décalage pendant
                    // l'édition : l'éditeur de MaxGraph applique une translation
                    // CSS en pourcentage de sa propre hauteur pour un alignement
                    // 'middle', hauteur qui varie légèrement tant que le texte
                    // n'a pas encore été redimensionné côté modèle.
                    verticalAlign: 'top',
                    // Recalcule automatiquement la taille de la cellule à partir
                    // du texte rendu dès la fin de l'édition (labelChanged).
                    autoSize: true,
                    isText: true,
                } as AppCellStyle,
            });
            graph.setSelectionCell(vertex);
        });
        return;
    }

    if (type === 'square-node') {
        graph.batchUpdate(() => {
            // Border ciblé par le point de drop : le nouveau border devient
            // son enfant plutôt que d'être inséré dans le parent par défaut.
            const size: [number, number] = [150, 120];
            const target = findContainingBorder(pt.x, pt.y, null, size[0] * size[1]);
            const dropParent = target ?? parent;
            const origin = absTopLeft(dropParent);
            const vertex = graph.insertVertex({
                parent: dropParent,
                value: '',
                position: [pt.x - origin.x, pt.y - origin.y],
                size,
                style: {
                    fillColor: '#fffacd',
                    strokeColor: '#000000',
                    strokeWidth: 1,
                    rounded: true,
                    isRectangle: true,
                } as AppCellStyle,
            });
            graph.orderCells(true, [vertex]);
            ensureBackgroundAtBottom();
            // Les objets sur lesquels le rectangle est créé en deviennent les
            // enfants (le rectangle grandit au besoin pour les contenir).
            captureOverlappingObjects(vertex);
            growAncestorBorders(vertex);
            graph.setSelectionCell(vertex);
        });
        return;
    }

    if (type === 'icon-node' && nodeIcon?.src && nodeSelector) {
        const nodeId = nodeSelector.value;
        const affectedPairs = collectNewEdgePairs(parent, () => {
            graph.batchUpdate(() => {
                const existing = model.getCell(nodeId) as Cell | null;
                if (existing) {
                    restoreMissingEdgesForNode(nodeId, parent);
                    graph.setSelectionCells([existing]);
                    return;
                }

                const node = _nodes.get(nodeId);
                if (!node) return;

                // Un objet déposé sur un border en devient l'enfant (le
                // border grandit pour l'englober) ; les liens restent
                // rattachés au parent par défaut, indépendamment de ce nid.
                const dropTarget = findContainingBorder(pt.x, pt.y, null);
                const vertexParent = dropTarget ?? parent;
                const vertexOrigin = absTopLeft(vertexParent);

                const newVertex = graph.insertVertex({
                    parent: vertexParent,
                    id: nodeId,
                    value: buildLabel(node),
                    position: [pt.x - vertexOrigin.x - 16, pt.y - vertexOrigin.y - 16],
                    size: [32, 32],
                    style: {
                        shape: 'image',
                        image: nodeIcon.src,
                        editable: false,
                        resizable: true,
                        verticalLabelPosition: 'bottom',
                        spacingTop: -15,
                    },
                });
                if (dropTarget) growAncestorBorders(newVertex);

                node.edges.forEach((edge) => {
                    const targetCell = model.getCell(edge.attachedNodeId) as Cell | null;
                    if (targetCell) {
                        graph.insertEdge({
                            parent,
                            value: '',
                            source: newVertex,
                            target: targetCell,
                            style: {
                                editable: false,
                                strokeColor: '#ff0000',
                                strokeWidth: 2,
                                startArrow: 'none',
                                endArrow: 'none',
                            },
                        });
                    }
                });

                graph.setSelectionCell(newVertex);
            });
        });
        refreshParallelEdges(affectedPairs);
    }
});

//-------------------------------------------------------------------------
// Zoom

const zoomInButton = document.getElementById('zoom-in-btn') as HTMLButtonElement | null;
const fitButton = document.getElementById('fit-btn') as HTMLElement | null;
const zoomOutButton = document.getElementById('zoom-out-btn') as HTMLButtonElement | null;

if (zoomInButton) zoomInButton.addEventListener('click', () => graph.zoomIn());
if (fitButton) fitButton.addEventListener('click', () => graph.getPlugin<FitPlugin>('fit')?.fit({ margin: 20 }));
if (zoomOutButton) zoomOutButton.addEventListener('click', () => graph.zoomOut());

//-------------------------------------------------------------------------
// Suppression avec Delete / Backspace ou le bouton "Delete"

// Supprimer un rectangle ne supprime pas son contenu : les enfants non
// sélectionnés sont ré-attachés au plus proche ancêtre qui survit (position
// absolue conservée) avant la suppression. Les liens enfants (dont les points
// d'inflexion sont relatifs au parent) sont décalés en conséquence.
function releaseChildrenOfDeletedRectangles(cells: Cell[]): void {
    const doomed = new Set<Cell>(cells);

    for (const cell of cells) {
        if (!isRectangleCell(cell)) continue;

        let newParent: Cell | null = cell.getParent();
        while (newParent && doomed.has(newParent)) newParent = newParent.getParent();
        if (!newParent) continue;

        for (const child of [...(cell.children ?? [])]) {
            if (doomed.has(child)) continue;

            if (child.isEdge()) {
                const shiftX = absTopLeft(cell).x - absTopLeft(newParent).x;
                const shiftY = absTopLeft(cell).y - absTopLeft(newParent).y;
                model.add(newParent, child);
                const geo = child.getGeometry()?.clone();
                if (geo) {
                    geo.points = geo.points?.map((p) => new Point(p.x + shiftX, p.y + shiftY)) ?? null;
                    if (geo.sourcePoint) geo.sourcePoint = new Point(geo.sourcePoint.x + shiftX, geo.sourcePoint.y + shiftY);
                    if (geo.targetPoint) geo.targetPoint = new Point(geo.targetPoint.x + shiftX, geo.targetPoint.y + shiftY);
                    model.setGeometry(child, geo);
                }
            } else {
                reparentCell(child, newParent);
            }
        }
    }
}

function deleteSelectedCells(): void {
    const cells = graph.getSelectionCells().filter((c) => !isBackgroundCell(c));
    if (cells.length === 0) return;

    const affectedPairs = collectAffectedPairs(cells);
    graph.batchUpdate(() => {
        releaseChildrenOfDeletedRectangles(cells);
        graph.removeCells(cells);
    });
    refreshParallelEdges(affectedPairs);
}

// Sur window en phase capture : s'exécute avant TOUT listener tiers
// (MaxGraph, Select2, jQuery...). Si le graphe a le focus conceptuel,
// on absorbe Delete/Backspace pour MaxGraph et on bloque le HTML.
window.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.key !== 'Delete' && event.key !== 'Backspace') return;

    if (graphHasFocus) {
        // Ne pas intercepter quand le graphe est en mode édition de texte
        if (graph.isEditing()) return;
        // Bloquer systématiquement l'HTML quand le graphe est actif
        event.preventDefault();
        event.stopPropagation();
        if (graph.getSelectionCells().filter((c) => !isBackgroundCell(c)).length > 0) {
            deleteSelectedCells();
        }
        return;
    }

    // Pas de focus graphe : laisser le navigateur gérer dans les champs de saisie.
    const target = event.target as HTMLElement | null;
    if (target?.tagName === 'INPUT' || target?.tagName === 'TEXTAREA' || target?.isContentEditable) return;

    if (graph.getSelectionCells().filter((c) => !isBackgroundCell(c)).length === 0) return;
    event.preventDefault();
    event.stopPropagation();
    deleteSelectedCells();
}, true);

document.getElementById('delete-btn')?.addEventListener('click', deleteSelectedCells);

//-------------------------------------------------------------------------
// CTRL+A : sélectionner tout

document.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.ctrlKey && event.key === 'a') {
        event.preventDefault();
        event.stopPropagation();
        graph.selectAll();
    }
});

//-------------------------------------------------------------------------
// Connexions / déconnexions

graph.setConnectable(false);
graph.isCellDisconnectable = () => false;

//-------------------------------------------------------------------------
// Group / ungroup

const groupButton = document.getElementById('group-btn') as HTMLButtonElement | null;
const ungroupButton = document.getElementById('ungroup-btn') as HTMLButtonElement | null;

// Un groupe contenant un rectangle doit rester en arrière-plan par rapport
// aux flèches qui ne font pas partie du groupe.
function groupContainsRectangle(group: Cell): boolean {
    return (group.children ?? []).some((c) => isRectangleCell(c));
}

if (groupButton) {
    groupButton.addEventListener('click', () => {
        const cells = graph.getSelectionCells().filter((c) => !isBackgroundCell(c));
        if (cells.length > 1) {
            const parent = graph.getDefaultParent();

            // MaxGraph peut redimensionner un enfant lors de son ajout au groupe
            // (constrainChild s'applique avant que le groupe n'ait sa taille finale).
            // On mémorise la géométrie d'origine pour la restaurer après coup.
            const originalGeometries = new Map<Cell, Geometry>();
            for (const cell of cells) {
                const geo = cell.getGeometry();
                if (geo) originalGeometries.set(cell, geo.clone());
            }

            graph.batchUpdate(() => {
                // insertVertex ajoute déjà le groupe au parent — pas besoin de addCell()
                const group = graph.insertVertex({
                    parent,
                    style: {fillColor: 'none', strokeColor: 'none', resizable: false},
                });
                graph.groupCells(group, 5, cells);

                const groupGeo = group.getGeometry();
                if (!groupGeo) return;

                for (const cell of cells) {
                    const original = originalGeometries.get(cell);
                    if (!original) continue;
                    model.setGeometry(cell, new Geometry(
                        original.x - groupGeo.x,
                        original.y - groupGeo.y,
                        original.width,
                        original.height,
                    ));
                }

                // Les rectangles du groupe doivent rester en arrière-plan par
                // rapport à ses nœuds (au sein du groupe) et aux liens qui ne
                // font pas partie du groupe (au niveau du canevas).
                const rectanglesInGroup = (group.children ?? []).filter((c) => isRectangleCell(c));
                if (rectanglesInGroup.length > 0) {
                    graph.orderCells(true, rectanglesInGroup);
                    graph.orderCells(true, [group]);
                    ensureBackgroundAtBottom();
                }

                graph.refresh();
                graph.setSelectionCell(group);
            });
        }
    });
}

if (ungroupButton) {
    ungroupButton.addEventListener('click', () => {
        const cells = graph.getSelectionCells();
        if (cells.length === 1) {
            const released = graph.ungroupCells(cells);

            // Les rectangles libérés doivent rester en arrière-plan par
            // rapport aux flèches qui ne faisaient pas partie du groupe.
            const rectangles = released.filter((c) => isRectangleCell(c));
            if (rectangles.length > 0) {
                graph.orderCells(true, rectangles);
                ensureBackgroundAtBottom();
            }

            graph.setSelectionCells(released);
        }
    });
}

// Déplacer un groupe peut le faire remonter au-dessus des flèches externes :
// on réapplique l'arrière-plan pour les groupes contenant un rectangle.
graph.addListener(InternalEvent.MOVE_CELLS, (_sender: unknown, evt: EventObject) => {
    const cells = evt.getProperty('cells') as Cell[] | undefined;
    if (!cells) return;

    const groups = cells.filter((c) => c.isVertex() && (c.children?.length ?? 0) > 0 && groupContainsRectangle(c));
    if (groups.length > 0) {
        graph.orderCells(true, groups);
        ensureBackgroundAtBottom();
    }
});

// Ré-parenté des objets déplacés à la souris (borders comme icônes) : tout
// objet déposé dans un border en devient l'enfant (le parent grandit pour
// l'englober) ; un objet sorti de son border parent redevient enfant du
// parent par défaut. On ne touche pas à l'appartenance à un Group (bouton
// Group) : celle-ci n'est pas régie par la géométrie d'un border.
graph.addListener(InternalEvent.MOVE_CELLS, (_sender: unknown, evt: EventObject) => {
    const cells = evt.getProperty('cells') as Cell[] | undefined;
    if (!cells) return;
    const movedObjects = cells.filter((c) => c.isVertex() && !isBackgroundCell(c));
    if (movedObjects.length === 0) return;

    graph.batchUpdate(() => {
        const root = graph.getDefaultParent();
        for (const obj of movedObjects) {
            const g = obj.getGeometry();
            if (!g) continue;
            const tl = absTopLeft(obj);
            const target = findContainingBorder(
                tl.x + g.width / 2, tl.y + g.height / 2, obj,
                isRectangleCell(obj) ? g.width * g.height : 0,
            );
            const currentParent = obj.getParent();

            if (target && target !== currentParent) {
                reparentCell(obj, target);
                growAncestorBorders(obj);
            } else if (!target && isRectangleCell(currentParent) && currentParent !== root) {
                reparentCell(obj, root);
            }
        }

        // Un border déplacé sur des objets les capture (même partiellement) :
        // ils en deviennent les enfants et suivront ses déplacements.
        for (const obj of movedObjects) {
            if (!isRectangleCell(obj)) continue;
            keepBorderBehind(obj);
            captureOverlappingObjects(obj);
            growAncestorBorders(obj);
        }
    });
    graph.refresh();
});

// Redimensionnement (CELLS_RESIZED, non couvert par le listener MOVE_CELLS
// ci-dessus) :
// - un border rétréci libère ses enfants entièrement sortis du rectangle ;
// - un border directement redimensionné doit ensuite contenir tous ses enfants
//   restants : on l'agrandit au minimum nécessaire si besoin ;
// - un enfant de border redimensionné fait grandir la chaîne parente ;
// - agrandir un border par le haut ou la gauche déplace son origine : ses
//   enfants (en coordonnées relatives) sont contre-décalés pour rester en place.
graph.addListener(InternalEvent.CELLS_RESIZED, (_sender: unknown, evt: EventObject) => {
    const cells = evt.getProperty('cells') as Cell[] | undefined;
    const prev = evt.getProperty('prev') as (Geometry | null)[] | undefined;
    if (!cells) return;

    graph.batchUpdate(() => {
        cells.forEach((c, i) => {
            const before = prev?.[i];
            const after = c.getGeometry();
            if (isRectangleCell(c) && before && after) {
                // Écart réel d'origine (et non celui demandé : MaxGraph peut
                // borner x/y à 0 si les coordonnées négatives sont interdites).
                const dx = after.x - before.x;
                const dy = after.y - before.y;
                if (dx !== 0 || dy !== 0) {
                    for (const child of c.children ?? []) {
                        if (child.isEdge()) continue;
                        const cg = child.getGeometry()?.clone();
                        if (!cg) continue;
                        cg.x -= dx;
                        cg.y -= dy;
                        child.setGeometry(cg);
                    }
                }
            }
        });
        for (const c of cells) {
            if (!isRectangleCell(c)) {
                growAncestorBorders(c);
                continue;
            }
            // Rétrécir un border ne doit pas être empêché : les enfants
            // entièrement sortis du rectangle en sont détachés d'abord...
            releaseChildrenOutsideBorder(c);
            keepBorderBehind(c);
            // ...puis il capture les objets désormais recouverts (même
            // partiellement) et grandit pour contenir entièrement ses enfants.
            captureOverlappingObjects(c);
            growAncestorBorders(c);
        }
    });
    graph.refresh();
});

//---------------------------------------------------------------------------
// Déplacement avec flèches

function moveSelectedVertices(graph: Graph, dx: number, dy: number): void {
    const vertices = graph.getSelectionCells().filter((c) => c.isVertex() && !isBackgroundCell(c));
    if (vertices.length === 0) return;

    graph.batchUpdate(() => {
        for (const vertex of vertices) {
            vertex.getGeometry()?.translate(dx, dy);
        }
        graph.refresh();
    });
}

document.addEventListener('keydown', (event: KeyboardEvent) => {
    // Laisser les flèches déplacer le curseur dans le texte en cours
    // d'édition (cellule ou champ de saisie normal de la page) plutôt que
    // de déplacer le sommet sélectionné.
    if (graph.isEditing()) return;
    const target = event.target as HTMLElement | null;
    if (target?.tagName === 'INPUT' || target?.tagName === 'TEXTAREA' || target?.isContentEditable) return;

    const step = 1;
    switch (event.key) {
        case 'ArrowUp':
            moveSelectedVertices(graph, 0, -step);
            break;
        case 'ArrowDown':
            moveSelectedVertices(graph, 0, step);
            break;
        case 'ArrowLeft':
            moveSelectedVertices(graph, -step, 0);
            break;
        case 'ArrowRight':
            moveSelectedVertices(graph, step, 0);
            break;
    }
});

//---------------------------------------------------------------------------
// Placement en cercle (double-clic)

function placeObjectsOnCircle(center: Pt, radius: number, n: number): Pt[] {
    const angleStep = (2 * Math.PI) / n;
    return Array.from({length: n}, (_, i) => ({
        x: center.x + radius * Math.cos(i * angleStep),
        y: center.y + radius * Math.sin(i * angleStep),
    }));
}

//----------------------------------------------------------------
// Double-clic sur icône

graph.addListener(InternalEvent.DOUBLE_CLICK, (_sender: unknown, evt: EventObject) => {
    const cell = evt.getProperty('cell') as Cell | null;
    if (!cell?.isVertex()) return;

    const style = cell.style;
    if (style?.shape !== 'image') return;

    const node = _nodes.get(cell.id as string);
    if (!node) return;

    const doubleClickParent = graph.getDefaultParent();
    const affectedPairs = collectNewEdgePairs(doubleClickParent, () => {
        graph.batchUpdate(() => {
            // Plusieurs liens peuvent exister entre les deux mêmes nœuds : on les
            // regroupe par cible pour n'insérer le sommet qu'une fois tout en
            // conservant chacun des liens (refreshParallelEdges les écartera en arc).
            const newEdgesByTarget = new Map<string, Edge[]>();
            const parent = graph.getDefaultParent();
            const filter = getFilter();
            const attrFilter = getAttrFilter();
            const direction = getDirection();

            node.edges.forEach((edge) => {
                const targetNode = _nodes.get(edge.attachedNodeId);
                if (!targetNode) return;
                if (model.getCell(edge.attachedNodeId)) return; // déjà présent : traité par la passe globale ci-dessous

                if (
                    (
                        filter.length === 0 ||
                        filter.includes(targetNode.vue) ||
                        (filter.includes('8') && edge.edgeType === 'CABLE') ||
                        (filter.includes('9') && edge.edgeType === 'FLUX') ||
                        (filter.includes('10') && edge.edgeType === 'LFLUX')
                    ) &&
                    matchesAttrFilter(targetNode, attrFilter) &&
                    matchesDirection(direction, node, targetNode)
                ) {
                    const list = newEdgesByTarget.get(edge.attachedNodeId);
                    if (list) list.push(edge); else newEdgesByTarget.set(edge.attachedNodeId, [edge]);
                }
            });

            const geom = cell.getGeometry();
            if (geom && newEdgesByTarget.size > 0) {
                const targetIds = Array.from(newEdgesByTarget.keys());
                const positions = placeObjectsOnCircle({x: geom.x, y: geom.y}, 80, targetIds.length);

                for (let i = 0; i < positions.length; i++) {
                    const attachedNodeId = targetIds[i];
                    const edgesToTarget = newEdgesByTarget.get(attachedNodeId)!;
                    const newNode = _nodes.get(attachedNodeId);
                    if (!newNode) continue;

                    const vertex = graph.insertVertex({
                        parent,
                        id: newNode.id,
                        value: buildLabel(newNode),
                        position: [positions[i].x, positions[i].y],
                        size: [32, 32],
                        style: {
                            shape: 'image',
                            image: newNode.image,
                            editable: false,
                            resizable: true,
                            verticalLabelPosition: 'bottom',
                            spacingTop: -15,
                        },
                    });

                    for (const edge of edgesToTarget) {
                        graph.insertEdge({
                            parent,
                            value: edge.name,
                            source: cell,
                            target: vertex,
                            style: buildEdgeStyle(edge),
                        });
                    }
                }
            }

            // Restaure les liens manquants entre le nœud double-cliqué et les
            // nœuds déjà présents dans le graphe.
            restoreMissingEdgesForNode(node.id, parent);
        });
    });
    refreshParallelEdges(affectedPairs);
    if (physicsEnabled) startPhysics();
});

//-------------------------------------------------------------------------
// Déploiement récursif (bouton Deploy)

function insertPlacedVertex(node: MapNode, position: Pt, parent: Cell): Cell {
    return graph.insertVertex({
        parent,
        id: node.id,
        value: buildLabel(node),
        position: [position.x - 16, position.y - 16],
        size: [32, 32],
        style: {
            shape: 'image',
            image: node.image,
            editable: false,
            resizable: true,
            verticalLabelPosition: 'bottom',
            spacingTop: -15,
        },
    });
}

function deployFromNode(
    nodeId: string,
    depth: number,
    visited: Set<string>,
    filter: string[],
    attrFilter: string[],
    direction: number,
    parent: Cell,
): void {
    if (depth <= 0 || visited.has(nodeId)) return;
    visited.add(nodeId);

    const node = _nodes.get(nodeId);
    const sourceCell = model.getCell(nodeId) as Cell | null;
    const geom = sourceCell?.getGeometry();
    if (!node || !sourceCell || !geom) return;

    const newEdgesByTarget = new Map<string, Edge[]>();
    for (const edge of node.edges) {
        if (model.getCell(edge.attachedNodeId)) continue; // déjà affiché
        const targetNode = _nodes.get(edge.attachedNodeId);
        if (!targetNode) continue;

        const passesFilter =
            filter.length === 0 ||
            filter.includes(targetNode.vue) ||
            (filter.includes('8') && edge.edgeType === 'CABLE') ||
            (filter.includes('9') && edge.edgeType === 'FLUX') ||
            (filter.includes('10') && edge.edgeType === 'LFLUX');

        if (
            !passesFilter ||
            !matchesAttrFilter(targetNode, attrFilter) ||
            !matchesDirection(direction, node, targetNode)
        ) continue;

        const list = newEdgesByTarget.get(edge.attachedNodeId);
        if (list) list.push(edge); else newEdgesByTarget.set(edge.attachedNodeId, [edge]);
    }

    if (newEdgesByTarget.size === 0) return;

    const targetIds = Array.from(newEdgesByTarget.keys());
    const positions = placeObjectsOnCircle({x: geom.x, y: geom.y}, 80, targetIds.length);

    targetIds.forEach((attachedNodeId, i) => {
        const edgesToTarget = newEdgesByTarget.get(attachedNodeId)!;
        const newNode = _nodes.get(attachedNodeId);
        if (!newNode) return;

        const vertex = insertPlacedVertex(newNode, positions[i], parent);

        for (const edge of edgesToTarget) {
            graph.insertEdge({
                parent,
                value: edge.name,
                source: sourceCell,
                target: vertex,
                style: buildEdgeStyle(edge),
            });
        }
    });

    for (const attachedNodeId of targetIds) {
        deployFromNode(attachedNodeId, depth - 1, visited, filter, attrFilter, direction, parent);
    }
}

document.getElementById('deploy-btn')?.addEventListener('click', (e) => {
    const selected = graph.getSelectionCell() as Cell | null;
    if (!selected || !_nodes.has(selected.id as string)) {
        const message = (e.currentTarget as HTMLElement).dataset.pleaseSelect ?? 'Please select a node.';
        alert(message);
        return;
    }

    const depthSelect = document.getElementById('depth') as HTMLSelectElement | null;
    const depth = parseInt(depthSelect?.value ?? '3', 10) || 3;

    const deployParent = graph.getDefaultParent();
    const deployAffectedPairs = collectNewEdgePairs(deployParent, () => {
        graph.batchUpdate(() => {
            const parent = graph.getDefaultParent();
            deployFromNode(selected.id as string, depth, new Set(), getFilter(), getAttrFilter(), getDirection(), parent);
            completeMissingEdgesAmongPlacedNodes(parent);
        });
    });
    refreshParallelEdges(deployAffectedPairs);
    if (physicsEnabled) startPhysics();
});

//-------------------------------------------------------------------------
// Ajout direct d'un objet sélectionné dans le sélecteur (#node)

document.getElementById('add-node-btn')?.addEventListener('click', () => {
    const nodeId = nodeSelector?.value;
    if (!nodeId) return;

    if (model.getCell(nodeId)) {
        const existingCell = model.getCell(nodeId) as Cell;
        const restoreParent = graph.getDefaultParent();
        const restoreAffectedPairs = collectNewEdgePairs(restoreParent, () => {
            graph.batchUpdate(() => {
                restoreMissingEdgesForNode(nodeId, restoreParent);
            });
        });
        refreshParallelEdges(restoreAffectedPairs);
        graph.setSelectionCells([existingCell]);
        return;
    }

    const node = _nodes.get(nodeId);
    if (!node) return;

    const addNodeParent = graph.getDefaultParent();
    const addNodeAffectedPairs = collectNewEdgePairs(addNodeParent, () => {
        graph.batchUpdate(() => {
            const parent = graph.getDefaultParent();

            // Centre de la partie VISIBLE du graphe (et non de son contenu, qui
            // peut être vide ou hors champ), converti en coordonnées modèle.
            const view = graph.getView();
            const center = {
                x: container.clientWidth / 2 / view.scale - view.translate.x,
                y: container.clientHeight / 2 / view.scale - view.translate.y,
            };

            const newVertex = graph.insertVertex({
                parent,
                id: node.id,
                value: buildLabel(node),
                position: [center.x - 16, center.y - 16],
                size: [32, 32],
                style: {
                    shape: 'image',
                    image: node.image,
                    editable: false,
                    resizable: true,
                    verticalLabelPosition: 'bottom',
                    spacingTop: -15,
                },
            });

            completeMissingEdgesAmongPlacedNodes(parent);
            graph.setSelectionCell(newVertex);
        });
    });
    refreshParallelEdges(addNodeAffectedPairs);
});

//-------------------------------------------------------------------------
// Réinitialisation du canevas

document.getElementById('reload-btn')?.addEventListener('click', () => {
    const cells = graph.getChildCells().filter((c) => !isBackgroundCell(c));
    if (cells.length > 0) graph.removeCells(cells);
});

//-------------------------------------------------------------------------
// Mise à jour des nœuds depuis _nodes

// Retrouve les données d'un lien (JSON _nodes) à partir de ses deux extrémités
function findEdgeData(sourceId: string, targetId: string): Edge | undefined {
    return (
        _nodes.get(sourceId)?.edges.find((e) => e.attachedNodeId === targetId) ??
        _nodes.get(targetId)?.edges.find((e) => e.attachedNodeId === sourceId)
    );
}

document.getElementById('update-btn')?.addEventListener('click', () => {
    graph.batchUpdate(() => {
        graph.getChildCells().forEach((cell) => {
            if (cell.isEdge()) {
                const s = cell.source, t = cell.target;
                if (!s || !t) return;
                const data = findEdgeData(String(s.id), String(t.id));
                if (!data || data.edgeType !== 'CABLE') return;

                // Un lien physique conserve sa couleur et son type lors de la mise à jour.
                cell.value = data.name;
                const style: CellStateStyle = { ...(cell.style ?? {}) };
                style.strokeColor = data.color ?? '#000000';
                style.strokeWidth = 2;
                model.setStyle(cell, style);
                return;
            }
            const style = styleOf(cell);
            if (style?.isBackground) return;
            if (!style?.image) return;

            const node = _nodes.get(cell.id as string);
            if (!node) {
                graph.removeCells([cell], true);
            } else {
                cell.value = buildLabel(node);
                styleUtils.setCellStyles(graph.getDataModel(), [cell], 'image', node.image);
            }
        });
        graph.refresh();
    });
});

//---------------------------------------------------------------------------
// Export SVG

document.getElementById('download-btn')?.addEventListener('click', () => downloadGraphSVG(graph));

//---------------------------------------------------------------------------
// Moteur physique (force-directed) start / stop
//---------------------------------------------------------------------------

let physicsRunning = false;
let physicsEnabled = false; // true uniquement si l'utilisateur a activé le magnet
let physicsRafId: number | null = null;
const physicsStartGeo = new Map<Cell, Geometry>();

// Distance minimale entre un nœud confiné et le bord de son groupe
const GROUP_EDGE_MARGIN = 10;

// Le label (sous l'objet, verticalLabelPosition: 'bottom') peut être sur
// plusieurs lignes (IP, tags) : on lit sa bounding box réellement rendue
// (gère le retour à la ligne, la police et le spacingTop sans approximation)
// pour calculer jusqu'où le pied de l'objet (icône + label) descend.
// Police et décalage réellement appliqués au label des objets (cf. styles
// d'insertion des sommets image : pas de fontSize explicite => défaut
// MaxGraph 11px ; spacingTop: -15 remonte le label dans l'icône).
const LABEL_FONT_SIZE = 11;
const LABEL_LINE_HEIGHT = LABEL_FONT_SIZE * 4;
const LABEL_SPACING_TOP = -15;

function getFootprintHeight(cell: Cell, geo: Geometry): number {
    const value = cell.value;
    if (typeof value !== 'string' || value.length === 0) return geo.height;

    const lines = value.split('\n').length;
    const labelHeight = lines * LABEL_LINE_HEIGHT;
    const extra = Math.max(0, labelHeight + LABEL_SPACING_TOP);

    return geo.height + extra;
}

// Arrêt automatique : mouvement (px/tick) sous ce seuil pendant N ticks de suite
const STABILITY_THRESHOLD = 0.4;
const STABILITY_TICKS_REQUIRED = 30;
let stableTicks = 0;

function isMovableObject(cell: Cell): boolean {
    if (!cell.isVertex()) return false;
    if (isRectangleCell(cell)) return false;   // un border ne bouge jamais en physique
    const s = styleOf(cell);
    const hasImage = s?.shape === 'image' && !!s?.image;
    const hasChildren = (cell.children?.length ?? 0) > 0;
    return hasImage && !hasChildren && !s?.isBackground;
}

function collectVertices(): Cell[] {
    const acc: Cell[] = [];
    const walk = (cell: Cell) => {
        for (const c of cell.children ?? []) {
            if (c.isVertex()) acc.push(c);
            if (c.children?.length) walk(c);
        }
    };
    walk(graph.getDefaultParent());
    return acc;
}

function physicsTick(): void {
    // Les conteneurs de groupe ne sont pas de vrais objets : les exclure des
    // forces de répulsion, sinon ils repoussent leurs propres enfants vers
    // les bords du groupe.
    const all = collectVertices().filter((c) => (c.children?.length ?? 0) === 0);
    const movable = all.filter(isMovableObject);
    if (movable.length === 0) {
        stopPhysics();
        return;
    }

    const pos = new Map<Cell, Point>();
    for (const v of all) pos.set(v, modelCenter(v));
    const force = new Map<Cell, Point>();
    for (const v of movable) force.set(v, new Point(0, 0));

    // Vitesse de réaction +50%
    const K_REP = 11500, K_SPRING = 0.06, REST = 120, DAMPING = 0.85, MAX_STEP = 30;

    for (const v of movable) {
        const pv = pos.get(v)!, f = force.get(v)!;
        for (const u of all) {
            if (u === v) continue;
            const pu = pos.get(u)!;
            const dx = pv.x - pu.x, dy = pv.y - pu.y;
            let d2 = dx * dx + dy * dy;
            if (d2 < 1) d2 = 1;
            const d = Math.sqrt(d2), rep = K_REP / d2;
            f.x += (dx / d) * rep;
            f.y += (dy / d) * rep;
        }
    }
    for (const edge of graph.getChildEdges(graph.getDefaultParent())) {
        const s = edge.source, t = edge.target;
        if (!s || !t) continue;
        const ps = pos.get(s) ?? modelCenter(s), pt = pos.get(t) ?? modelCenter(t);
        const dx = pt.x - ps.x, dy = pt.y - ps.y;
        const d = Math.hypot(dx, dy) || 1, fmag = K_SPRING * (d - REST);
        const fx = (dx / d) * fmag, fy = (dy / d) * fmag;

        // Un lien qui sort d'un groupe ne doit pas influencer l'élément
        // qui se trouve dans ce groupe (seuls les liens internes au même
        // groupe agissent sur ses membres).
        const sParent = s.getParent(), tParent = t.getParent();
        const sConfined = !!sParent && sParent !== graph.getDefaultParent();
        const tConfined = !!tParent && tParent !== graph.getDefaultParent();
        const externalToS = sConfined && sParent !== tParent;
        const externalToT = tConfined && tParent !== sParent;

        if (force.has(s) && !externalToS) {
            const f = force.get(s)!;
            f.x += fx;
            f.y += fy;
        }
        if (force.has(t) && !externalToT) {
            const f = force.get(t)!;
            f.x -= fx;
            f.y -= fy;
        }
    }

    // Rappel vers le centre du groupe pour les objets confinés : sans cette
    // force, la répulsion pure n'a rien pour la contrebalancer et envoie
    // systématiquement les objets contre les bords du groupe.
    const K_CENTER = 0.02;
    for (const v of movable) {
        const p = v.getParent();
        if (!p || p === graph.getDefaultParent()) continue;
        const pg = p.getGeometry();
        const geo0 = v.getGeometry();
        if (!pg || !geo0) continue;
        const f = force.get(v)!;
        const targetX = (pg.width - geo0.width) / 2;
        const targetY = (pg.height - geo0.height) / 2;
        f.x += (targetX - geo0.x) * K_CENTER;
        f.y += (targetY - geo0.y) * K_CENTER;
    }

    // Groupes existants : un nœud qui n'en fait pas partie ne doit jamais y
    // entrer et doit rester à au moins 10px de son bord.
    const groups = graph.getChildVertices(graph.getDefaultParent()).filter((c) => (c.children?.length ?? 0) > 0);

    let maxMovement = 0;

    graph.batchUpdate(() => {       // n'empile rien tant que physicsSuppressUndo est vrai
        for (const v of movable) {
            const f = force.get(v)!;
            let dx = f.x * DAMPING, dy = f.y * DAMPING;
            const m = Math.hypot(dx, dy);
            if (m > maxMovement) maxMovement = m;
            if (m > MAX_STEP) {
                dx = dx / m * MAX_STEP;
                dy = dy / m * MAX_STEP;
            }
            const geo = v.getGeometry()?.clone();
            if (!geo) continue;
            geo.x += dx;
            geo.y += dy;
            const footprintHeight = getFootprintHeight(v, geo);

            const p = v.getParent();
            if (p && p !== graph.getDefaultParent()) {
                const pg = p.getGeometry();
                if (pg) {
                    // L'icône doit garder 10px du bord du groupe ; le label sous
                    // l'objet (potentiellement multi-lignes) peut s'en approcher
                    // davantage, tant qu'il reste entièrement dans le groupe.
                    const minX = GROUP_EDGE_MARGIN, maxX = Math.max(minX, pg.width - geo.width - GROUP_EDGE_MARGIN);
                    const minY = GROUP_EDGE_MARGIN;
                    const maxY = Math.max(minY, pg.height - Math.max(geo.height + GROUP_EDGE_MARGIN, footprintHeight));
                    geo.x = Math.max(minX, Math.min(geo.x, maxX));
                    geo.y = Math.max(minY, Math.min(geo.y, maxY));
                }
            } else {
                // Un nœud hors groupe ne doit jamais entrer dans un groupe
                // existant : on l'éjecte vers le bord le plus proche, à 10px.
                for (const group of groups) {
                    const gg = group.getGeometry();
                    if (!gg) continue;
                    const gx = gg.x - GROUP_EDGE_MARGIN, gy = gg.y - GROUP_EDGE_MARGIN;
                    const gw = gg.width + 2 * GROUP_EDGE_MARGIN, gh = gg.height + 2 * GROUP_EDGE_MARGIN;

                    const overlapLeft = (geo.x + geo.width) - gx;
                    const overlapRight = (gx + gw) - geo.x;
                    const overlapTop = (geo.y + footprintHeight) - gy;
                    const overlapBottom = (gy + gh) - geo.y;

                    if (overlapLeft > 0 && overlapRight > 0 && overlapTop > 0 && overlapBottom > 0) {
                        const minOverlap = Math.min(overlapLeft, overlapRight, overlapTop, overlapBottom);
                        if (minOverlap === overlapLeft) geo.x = gx - geo.width;
                        else if (minOverlap === overlapRight) geo.x = gx + gw;
                        else if (minOverlap === overlapTop) geo.y = gy - footprintHeight;
                        else geo.y = gy + gh;
                    }
                }
            }
            v.setGeometry(geo);
        }
    });
    graph.refresh();

    // Arrêt automatique une fois le graphe stabilisé (plus de mouvement notable).
    if (maxMovement < STABILITY_THRESHOLD) {
        stableTicks++;
        if (stableTicks >= STABILITY_TICKS_REQUIRED) {
            stopPhysics();
            return;
        }
    } else {
        stableTicks = 0;
    }

    if (physicsRunning) physicsRafId = requestAnimationFrame(physicsTick);
}

function setPhysicsButtonActive(active: boolean): void {
    const btn = document.getElementById('physics-btn');
    if (!btn) return;
    btn.setAttribute('aria-pressed', String(active));
    btn.classList.toggle('bi-magnet', !active);
    btn.classList.toggle('bi-magnet-fill', active);
}

function startPhysics(): void {
    if (physicsRunning) return;
    physicsStartGeo.clear();
    stableTicks = 0;
    for (const v of collectVertices().filter(isMovableObject)) {
        const g = v.getGeometry();
        if (g) physicsStartGeo.set(v, g.clone());
    }
    physicsSuppressUndo = true;     // les ticks ne créent aucun état
    physicsRunning = true;
    setPhysicsButtonActive(true);
    physicsRafId = requestAnimationFrame(physicsTick);
}

function stopPhysics(): void {
    // Toujours resynchroniser physicsEnabled avec l'état réel (bouton relâché),
    // y compris lors d'un arrêt automatique (stabilisation, interaction
    // manuelle) : sinon un clic sur l'icône bascule physicsEnabled à false
    // (rappelant stopPhysics en pure perte) et il faut un second clic pour
    // relancer réellement le moteur.
    physicsEnabled = false;
    physicsRunning = false;
    if (physicsRafId !== null) {
        cancelAnimationFrame(physicsRafId);
        physicsRafId = null;
    }
    setPhysicsButtonActive(false);

    if (physicsStartGeo.size === 0) {
        physicsSuppressUndo = false;
        return;
    }

    // Positions finales atteintes
    const finalGeo = new Map<Cell, Geometry>();
    for (const cell of physicsStartGeo.keys()) {
        const g = cell.getGeometry();
        if (g) finalGeo.set(cell, g.clone());
    }
    // (a) revenir au départ SANS enregistrer (drapeau encore actif)
    graph.batchUpdate(() => {
        for (const [cell, g0] of physicsStartGeo) cell.setGeometry(g0.clone());
    });
    // (b) ré-appliquer le final EN enregistrant → un seul état undo
    physicsSuppressUndo = false;
    graph.batchUpdate(() => {
        for (const [cell, g1] of finalGeo) cell.setGeometry(g1);
        refreshParallelEdges();     // courbures incluses dans la même transaction
    });
    graph.refresh();
    physicsStartGeo.clear();
}

document.getElementById('physics-btn')?.addEventListener('click', () => {
    physicsEnabled = !physicsEnabled;
    physicsEnabled ? startPhysics() : stopPhysics();
});

// Toute interaction manuelle (clic, déplacement) doit arrêter le moteur
// physique au préalable : sinon physicsSuppressUndo, encore actif pendant
// l'animation, avale aussi l'édition undo-able de l'utilisateur (le ctrl+Z
// du déplacement manuel ne fonctionnait plus tant que la physique tournait).
graph.addMouseListener({
    mouseDown(_sender, _me) {
        if (physicsRunning) stopPhysics();
    },
    mouseMove(_sender, _me) {
    },
    mouseUp(_sender, _me) {
    },
});

//---------------------------------------------------------------------------
// Arrière-plan de carte
//---------------------------------------------------------------------------

const BACKGROUND_ID = '__background__';

function setBackground(image: string, w: number, h: number): void {
    graph.batchUpdate(() => {
        const parent = graph.getDefaultParent();
        let bg = model.getCell(BACKGROUND_ID) as Cell | null;
        if (bg) {
            const style: AppCellStyle = { ...(bg.style ?? {}) };
            style.image = image;
            model.setStyle(bg, style);
            const geo = bg.getGeometry()?.clone();
            if (geo) {
                geo.width = w;
                geo.height = h;
                bg.setGeometry(geo);
            }
        } else {
            bg = graph.insertVertex({
                parent, id: BACKGROUND_ID, value: '', position: [0, 0], size: [w, h],
                style: {shape: 'image', image, isBackground: true, editable: false} as AppCellStyle,
            });
        }
        graph.orderCells(true, [bg!]);
    });
    graph.refresh();
}

function applyBackgroundFromSource(src: string): void {
    const img = new Image();
    img.onload = () => setBackground(src, img.naturalWidth, img.naturalHeight);
    img.src = src;
}

function selectDefaultBackground(url: string): void {
    applyBackgroundFromSource(url);
}

const backgroundMenu = document.getElementById('background-menu') as HTMLDivElement | null;

document.getElementById('background-btn')?.addEventListener('click', (e) => {
    if (!backgroundMenu) return;
    const target = e.currentTarget as HTMLElement;
    const rect = target.getBoundingClientRect();

    // position:absolute se positionne par rapport à l'ancêtre positionné
    // (#editor, position:relative), pas par rapport au viewport. On ne peut
    // pas utiliser backgroundMenu.offsetParent ici : il vaut null tant que
    // le menu est display:none, soit précisément au moment de l'ouvrir.
    const editorRect = document.getElementById('editor')?.getBoundingClientRect();
    const offsetLeft = editorRect?.left ?? 0;
    const offsetTop = editorRect?.top ?? 0;

    backgroundMenu.style.left = `${rect.right - offsetLeft + 8}px`;
    backgroundMenu.style.top = `${rect.top - offsetTop}px`;
    backgroundMenu.style.display = backgroundMenu.style.display === 'block' ? 'none' : 'block';
});

document.querySelectorAll<HTMLElement>('.background-thumb').forEach((thumb) => {
    thumb.addEventListener('click', () => {
        const url = thumb.dataset.url;
        if (url) selectDefaultBackground(url);
        if (backgroundMenu) backgroundMenu.style.display = 'none';
    });
});

document.getElementById('background-input-btn')?.addEventListener('click', () => {
    document.getElementById('background-input')?.click();
});

document.getElementById('background-input')?.addEventListener('change', (e) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        applyBackgroundFromSource(reader.result as string);
        if (backgroundMenu) backgroundMenu.style.display = 'none';
    };
    reader.readAsDataURL(file);
});

document.getElementById('background-remove-btn')?.addEventListener('click', () => {
    const bg = model.getCell(BACKGROUND_ID) as Cell | null;
    if (bg) graph.removeCells([bg], true);
    if (backgroundMenu) backgroundMenu.style.display = 'none';
});

document.addEventListener('click', (event) => {
    const target = event.target as globalThis.Node | null;
    if (
        backgroundMenu &&
        !backgroundMenu.contains(target) &&
        target !== document.getElementById('background-btn')
    ) {
        backgroundMenu.style.display = 'none';
    }
});
