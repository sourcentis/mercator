# Cairn Diagram

The **Cairn diagram** automatically generates an application view from a selection of mapped objects: applications, application services, modules, databases, flows and entities.

Unlike [maps](maps.md), which you compose by hand, the Cairn diagram is **computed**: you pick a few starting objects, Mercator infers the related objects and the flows between them, and the diagram is drawn using the [Cairn](https://github.com/R0kshan/cairn/) notation.

This mode is ideal for:

- quickly getting the architecture diagram of an application;
- visualising exchanges between several applications;
- documenting an application scope without drawing every box;
- checking the consistency of the flows recorded in the map.

🔍 See also the [Exploration and Dependencies](features.md) tools

## Access

The tool is available under *Tools ➡️ Cairn diagram*.

It requires the `cairn_access` permission, which can be ticked in the **Tools** section of the [role management](roles.md) page. The **Tools** menu itself is only visible with the `tools_access` permission.

When upgrading to the version that introduces this tool, the `cairn_access` permission is automatically granted to every role that already has `explore_access`.

Proposed and drawn objects follow the user's [perimeter](perimeters.md) and [cartographer](cartographers.md) rights: an object outside the perimeter appears neither in the search nor in the diagram.

---

## Interface

| UI Element             | Type            | Role                                                                     |
|------------------------|-----------------|--------------------------------------------------------------------------|
| **Object type**        | Dropdown list   | Type of object to search for (entity, application, service, module, database, flow) |
| **Object**             | Searchable list | Selects the object to add (search on name, 50 results maximum)           |
| **Add**                | Blue button     | Adds the object to the selection                                         |
| **Clear**              | Red button      | Empties the whole selection                                              |
| **Selected objects**   | List            | Starting objects of the diagram, each with a remove button               |
| **Diagram**            | Drawing area    | Generated diagram, scrollable when wider than the page                   |
| **Documentation**      | Link            | Opens the Cairn notation documentation                                   |

The diagram is **recomputed on every change** to the selection: there is no generate button.

If the Cairn notation detects an inconsistency, an error (red) or warning (orange) message is displayed above the diagram.

---

## Building the Diagram

### What Each Object Type Adds

| Selected object         | What is drawn                                                                                 |
|-------------------------|-----------------------------------------------------------------------------------------------|
| **Application**         | The application, its application services and the modules of those services, plus all their flows |
| **Entity**              | The entity (external node) and every application it uses, expanded as above                  |
| **Application service** | The service alone (without its modules) and its flows                                         |
| **Application module**  | The module and its flows                                                                      |
| **Database**            | The database and its flows                                                                    |
| **Application flow**    | Both ends of the flow and the arrow between them                                              |

### Objects at the End of Flows

When a flow links a selected object to another object, the latter is **drawn** so that the arrow has a destination, but it is **not expanded**: its own services, modules and flows are not added.

To expand that object, simply add it to the selection.

### Nesting of Services and Modules

A service or module is drawn **inside** its application when that application is itself present in the diagram.

If it has no owning application in the diagram, or several, it is drawn as a standalone **grey box**. A legend note explains this.

### Application Blocks

Applications that belong to an **application block** are grouped in a **yellow** frame carrying the block's name. A legend note explains this.

---

## Representation

| Mercator object            | Representation in the diagram                                        |
|----------------------------|----------------------------------------------------------------------|
| Application block          | Yellow frame containing its applications                             |
| Application                | Box containing its services and modules                              |
| External application       | External node (if it contains no drawn service or module)            |
| Application service / module | Box nested inside its application, or grey box when orphaned       |
| Database                   | Data store                                                           |
| External database          | External node                                                        |
| Entity                     | External node, linked to its applications by a dashed "utilise" (uses) arrow |
| Application flow           | Arrow from source to destination, labelled with the flow's name (or its type) |
| Bidirectional flow         | Two arrows, one in each direction                                    |

An application or database is considered **external** when its *External* attribute contains the word "extern" (case-insensitive), for example "External" or "Externe".

A flow whose source or destination is not drawn (object outside the perimeter or deleted) is not displayed.

---

## Examples

### Architecture of an Application

> *"What are the components of HR-Solution and what does it exchange with?"*

1. Object type: **Applications**
2. Object: `HR-Solution` ➡️ **Add**

The diagram shows HR-Solution with its services and modules, plus every application and database it exchanges flows with.

### Exchanges Between Two Applications

> *"How do HR-Solution and Payroll communicate?"*

1. Add the application `HR-Solution`
2. Add the application `Payroll`

Both applications are expanded; the flows linking them appear between their services and modules.

### Applications Used by an Entity

> *"Which applications does the Finance Department use?"*

1. Object type: **Entities**
2. Object: `Finance Department` ➡️ **Add**

The entity is linked by a dashed arrow to each of its applications, which are expanded with their flows.

### A Specific Flow

> *"Between which components does the Salary-Export flow run?"*

1. Object type: **Application Flows**
2. Object: `Salary-Export` ➡️ **Add**

Only the two ends of the flow and the arrow between them are drawn.

---

## Best Practices

- **Start small**: one or two applications are often enough; then add the objects at the end of flows to expand them.
- **Record application ↔ service ↔ module links**: without them, services and modules appear in grey, outside their application.
- **Name your flows**: the flow name is used as the arrow label; otherwise its type is used.
- **Use application blocks** to visually group the applications of the same domain.
- **Fill in the *External* attribute** of applications and databases hosted outside the organisation to distinguish them in the diagram.
