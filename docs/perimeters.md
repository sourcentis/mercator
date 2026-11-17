# Perimeters in Mercator

Perimeters let you map several entities (establishments, sites, business units) in a single Mercator instance. Each entity manages its own objects, while still being able to link them to those of other perimeters.

This documentation explains what a perimeter is, how it combines with roles, and how users work within their perimeter.

## Introduction — What is a perimeter?

A **scope** groups together a subset of the mapped objects, regardless of their type. Each object (server, application, network, site, etc.) belongs to only one scope.

Perimeters allow you to:

- Host the cartography of **multiple entities** in a single instance
- Let each entity **manage its own objects** independently
- Identify the **flows and physical links** connecting entities
- Partition visibility and access control by entity

**Note:** perimeter management is an **optional feature**. Until you enable it, all objects remain in the default perimeter and no filtering or partitioning of the cartography is applied.

!!! info "Three access control mechanisms"
    Mercator combines three complementary notions:
    
    - The **perimeter** defines *which entity's objects* these rights apply to
    - The [role](roles.md) defines *what* the user can do within a perimeter
    - The assignment of a [cartographer](cartographers.md) delegates responsibility for **specific individual objects**, regardless of perimeter
    
    Roles and perimeters work together: a user's permissions in a perimeter are determined by their **role** in that **perimeter**.

!!! note "Technical model"
    - Each object carries a `perimeter_id` integer (never NULL, always ≥ 1)
    - A default perimeter always exists with `id = 1` (created by the database bootstrap migration)
    - Object names are **unique per (perimeter_id, name)** — two entities can each have a "DNSServer"
    - Perimeters are managed in a dedicated `perimeters` table

## How perimeters work: before and after activation

### Without perimeter management (default state)

When perimeter management is **disabled**:

- Every object has `perimeter_id = 1` (the default perimeter)
- **No filtering is applied** — all users see all objects
- There is no perimeter selector in the interface
- The feature is completely transparent

This is the default state of all existing Mercator instances.

### With perimeter management (after activation)

When you **enable** perimeter management from **Administration → Configuration → Perimeters**:

- Filtering is **activated**
- Users only see objects in the perimeters their roles grant them access to
- A **perimeter selector** appears (if the user has several perimeters)
- Administrators can **create new perimeters** (id > 1)
- Administrators can **move objects** between perimeters

!!! note "Important"
    Enabling perimeters does not modify existing data. All objects remain assigned to the default perimeter (id = 1). Partitioning only takes effect when you create new perimeters and reassign objects to them.

### The default perimeter

- **Always exists** with `id = 1` (created by the database bootstrap migration)
- Cannot be deleted
- Has a name that you define (initially "Default")
- Cannot be renamed to an empty string
- Is the perimeter of all objects until you explicitly move them

## Enabling perimeter management

Perimeter management is **disabled by default**. To enable it:

1. Go to **Administration → Configuration → Perimeters**
2. Switch **Enable perimeter management** to ON
3. Optionally, rename the default perimeter (e.g. "Head Office")
4. Save

### What happens when you enable it?

✓ Filtering is activated (users only see the perimeters assigned to them)  
✓ The perimeter selector appears for multi-perimeter users  
✓ Administration tools to create/rename/delete perimeters become available  
✓ The object assignment interface gets a perimeter drop-down list  
✓ All existing objects remain in the default perimeter (id = 1)  

**Nothing is hidden yet** — until you have created new perimeters and reassigned objects, all users still see everything.

### What happens when you disable it?

✓ Filtering is deactivated  
✓ All users see all objects, regardless of their perimeter assignment  
✓ The perimeter selector disappears from the interface  
✓ Perimeter administration tools are hidden  
✓ All objects keep their `perimeter_id` (data is not modified)  

You can enable and disable the feature without any risk of data loss.

## Managing perimeters

From **Administration → Configuration → Perimeters**, an administrator can:

### Add a perimeter

1. Click **+ Add a perimeter**
2. Enter a **name** (2 to 32 characters)
3. Click **Create**

The perimeter receives a numeric `id` (starting from 2). Since object names are unique per perimeter, two establishments can each have a "DNS Server".

### Rename a perimeter

1. Find the perimeter in the list
2. Click **Edit**
3. Change the name
4. Save

Even the default perimeter can be renamed (e.g. "Head Office").

### Delete a perimeter

A perimeter can only be deleted if:

- It contains **no objects** (reassign them first)
- It has **no roles** (reassign users to other roles first)
- It is **not the default perimeter** (id = 1 cannot be deleted)

To delete:

1. Reassign all objects to other perimeters or to the default perimeter
2. Reassign all roles to other perimeters or to the default perimeter
3. Click **Delete** in the perimeter list
4. Confirm

Deletion is **permanent** and cannot be undone.

!!! warning "Before deleting a perimeter"
    - Audit: check whether any objects or roles are still assigned to it
    - Notify the users of this perimeter
    - Migrate data to another perimeter if necessary

## Roles and perimeters

Each **role** is assigned to **exactly one perimeter**. The role's permissions only apply to objects in that perimeter. (See the *Roles* documentation for full details on role management.)

### Assigning a perimeter to a role

1. **Administration → Roles** → Create or Edit
2. Select a **Perimeter** (drop-down list)
3. Select permissions as usual
4. Save

### Multi-perimeter access

A **user** can hold several roles, each in a different perimeter. The perimeters accessible to the user are the **union of the perimeters of all their roles**.

#### Example: multi-establishment team

Alice is responsible for two establishments:

- **Role "Admin"** in Perimeter **1** ("Head Office")
  → Full access to all Head Office objects

- **Role "Reader"** in Perimeter **2** ("Branch A")
  → Read-only access to all Branch A objects

**Result:** Alice sees objects from both perimeters. She can edit Head Office objects but can only view those of Branch A. If a flow connects the two, she sees it from both sides.

### Administrator access

**Administrators** are never filtered by perimeter. They always see the entire cartography, regardless of which perimeters exist or how many there are.

### What happens when perimeter management is disabled?

When you **disable** perimeter management:

- All role perimeter assignments remain unchanged in the database
- But **no filtering is applied** — all users see all objects
- You can re-enable the feature later, and filtering will resume

This lets you enable and disable the feature without losing the role configuration.

!!! tip "Working across several establishments"
    Since a role carries a single perimeter, a user who must work in several establishments is given **several roles**, one per perimeter. This also makes it possible to combine a *read-only* role in one perimeter with a *read-write* role in another.

!!! info "Seeing everything is reserved to administrators"
    There is no "super" perimeter that would see all the others. A user who must oversee **the whole** cartography across all establishments is either an **administrator**, or holds a role in each perimeter.

## The working perimeter (selector)

When a user is responsible for **more than one perimeter**, Mercator displays a **perimeter selector** in the interface (just above the search field).

### What the selector does

The selector offers:

- An entry for **all your perimeters** (no filtering)
- Each perimeter in which you have a role

Selecting a perimeter has two effects:

1. **Filters** the current page to show only the objects of that perimeter
2. Becomes the **default perimeter** for any new object you create

### Selector behavior

| User situation | What they see |
|---|---|
| A single perimeter (1 role) | No selector (perimeter name displayed as text) |
| Several perimeters (several roles) | Drop-down list with all accessible perimeters |
| Perimeter management not enabled | No selector |
| Administrator | Sees all objects (no filtering) |

!!! info "Changing perimeter reloads the page"
    When you change the working perimeter, the current page is reloaded. Unsaved form data is lost. If the current record is outside the new perimeter, the page displays "not found" (403 Forbidden).

!!! note "The current choice is kept for the session"
    The selected perimeter is kept for the whole duration of your login session. At your next login, it reverts to the default value (all perimeters or the single perimeter).

## Creating and moving objects between perimeters

### When creating an object

If you have **more than one perimeter**, a **Perimeter** drop-down list appears in the creation form (before the Name field).

- It is initialized to your **current working perimeter**
- You can only select perimeters you have access to
- Validation checks that the selected perimeter is writable

### When editing an object

If you have **more than one perimeter**, the **Perimeter** drop-down list shows the object's current perimeter.

You can:

- **View** the object's perimeter assignment
- **Move** it to another perimeter (if you have write access to both)
- The name must remain unique within the target perimeter

After the move, the object's audit log records the change.

### Editing across your perimeters

You can only edit objects in perimeters for which you have a **role with write permissions**. Read-only roles do not allow moving or reassigning objects.

Example:

- Role "Admin" in perimeter 1 → can move objects in perimeter 1
- Role "Reader" in perimeter 2 → cannot move objects in perimeter 2 (read-only)

### Object identity in the interface

When an object is moved to a perimeter other than the default one, its identity is displayed as:

```
[Perimeter name] / Object name
```

Example: `[Branch A] / prod-db-01`, to quickly show which perimeter it belongs to.

!!! info "You can only use your own perimeters"
    A user can only assign an object to a perimeter they are responsible for. This rule is enforced when the form is saved, not only in the drop-down list.

!!! tip "The same name can be reused across perimeters"
    Object names are unique **within a perimeter**. Two different establishments can each have an object named, for example, *"DNS Server"*, without conflict.

## Application flows and physical links between establishments

### What are cross-perimeter flows?

A flow can connect two objects belonging to **different perimeters**. This makes it possible to **identify interactions between establishments** while keeping each establishment's object list separate.

Example:

```
Head Office DNS Server (Perimeter 1)
    ↓ resolves for ↓
Branch A Mail Server (Perimeter 2)
```

### Who sees a cross-perimeter flow?

Application flows and physical links **connect two objects that may belong to different perimeters**. Rather than belonging to a single perimeter, such a link is **visible from every perimeter it touches**.

A flow is **visible** to users of **any perimeter it touches**:

- Users with roles in Perimeter 1 see the flow (from the Head Office's point of view)
- Users with roles in Perimeter 2 see the flow (from Branch A's point of view)
- Users of other perimeters do NOT see the flow

This is what makes it possible to **identify flows between applications of different establishments**: the same flow appears to the teams of both establishments, each seeing it from its own side.

### Local objects and remote objects

In a flow connecting perimeters, you see:

- **Local objects** (in your current working perimeter or visible through your roles)
  → displayed in **full detail** (name, type, all attributes)

- **Remote objects** (in a perimeter you do not manage)
  → displayed as a **reference card** (name + perimeter tag)
  → you cannot edit remote objects directly
  → clicking on them does NOT lead to the full record (403 forbidden)

Example (for a Head Office administrator):

```
My DNS server (full detail, editable)
    ↓ resolves for ↓
[Branch A] / Mail relay (reference card, read-only)
```

!!! info "Records from another perimeter appear as a reference"
    When a flow points to an object located in a perimeter you do not manage, that remote object is displayed as a **short reference** (its name and perimeter) rather than its full record, so that the flow remains readable without exposing the other establishment's details.

### Creating flows between perimeters

You can create a flow to any object you can **see**:

- Objects in the perimeters you manage
- Objects for which you are the designated **cartographer**
- Remote objects (only if you have a read role in that perimeter)

However, **at least one endpoint must be in a perimeter you can edit**. You cannot create an orphan flow between two perimeters to which you only have read access.

### Audit and ownership

The flow's creator and creation date are recorded. The flow belongs to the creator's primary perimeter (the first perimeter they manage, or the one where the primary endpoint is located).

!!! info "Flows are not deleted when you delete a perimeter"
    If you delete a perimeter, its objects are deleted, but flows pointing to those objects remain (with broken references). Plan your deletions carefully.

## Importing data with perimeters (GLPI)

When Mercator synchronizes data from GLPI, imported objects are automatically assigned to a **target perimeter** defined in the connector configuration.

### Configuration

In the GLPI connector settings:

1. Select a **Target perimeter** (drop-down list of available perimeters)
2. Leave empty to use the **default perimeter** (id = 1)
3. Save

All objects imported or updated from this GLPI instance will have the specified `perimeter_id`.

### Reconciliation

The GLPI connector uses **two keys** to match existing objects:

- **The GLPI ID** (stored as `[glpi_id:NNN]` in the object description)
- **The perimeter** (from the connector configuration)

This means that:

- The same GLPI ID in different perimeters = **different Mercator objects**
- The same name in different perimeters = **different Mercator objects** (names are unique per perimeter, not globally)

### Re-import behavior

**When you run the synchronization again:**

1. **Existing objects are updated in place** (non-destructive synchronization)
   - Same GLPI ID + same perimeter → update of the existing record
   - New GLPI ID or new perimeter → creation of a new record

2. **If you change the connector's target perimeter:**
   - Already imported objects stay in their original perimeter
   - Newly imported objects land in the new perimeter
   - Result: objects from the same GLPI instance are spread across several perimeters

3. **To move imported objects to another perimeter:**
   - Manually edit each object in Mercator and reassign the perimeter, OR
   - Delete the connector, change the target perimeter and run the synchronization again (this creates duplicates, so not recommended)

!!! tip "Keep the GLPI → Mercator perimeter mapping stable"
    Decide on your GLPI → Mercator perimeter mapping **before** the first import. If you need to change it later, plan the migration carefully to avoid spreading objects across unintended perimeters.

## Security: perimeter boundaries and access control

### Principle: users see what they are responsible for

By default, users can only see and edit objects in the perimeters for which they have been assigned a role. Perimeter boundaries are enforced at every layer:

- At query level (automatic scope filtering)
- At policy level (authorization gates)
- In form validation (objects can only be assigned to your own perimeters)

### What users cannot do

- **View** objects outside their assigned perimeters (except remote references in flows)
- **Edit, move or delete** objects outside their assigned perimeters
- **Create a flow** to an object in a perimeter they do not have access to
- **Assign** an object to a perimeter for which they do not have write access
- **Access perimeter management** (reserved to administrators)

### Enforcement

```php
// Query scope: automatic filtering on every read
Object::whereIn('perimeter_id', $user->rolePerimeters())
      ->get();

// Policy gate: before any write
Gate::denies('update', $object) 
    // Checks: is the object in one of the user's perimeters?
    abort_if(..., 403);

// Form validation: on create/update
$request->validate([
    'perimeter_id' => 'in:' . implode(',', $user->rolePerimeters())
]);
```

### Cartographer assignments

A **cartographer** is responsible for a specific individual object, regardless of perimeter. A cartographer:

- **Always sees** the object assigned to them (even if it is in another perimeter)
- Can **edit** the object if their role grants them write permissions in that perimeter
- Cannot move the object to another perimeter (unless they also have a role there)

### Audit trail

Every move of an object between perimeters is logged:

- **Who** moved it (user + role)
- **When** (timestamp)
- **What** (object name + old perimeter → new perimeter)

Administrators can review changes in the **Audit log** (Administration → Logs).

## Best practices

### Before enabling perimeter management

1. **Identify your establishments/units**
   - Each autonomous, self-managed unit = 1 perimeter
   - Avoid creating perimeters for organizational convenience (e.g. by team or by function)
   - Do not create "empty" perimeters in anticipation

2. **Plan your role structure**
   - Map each team/user to the perimeters they manage
   - Decide on permission levels (Admin, Editor, Reader, etc.)
   - Identify shared roles (e.g. "Global read-only" for executives)
   - See the *Roles* documentation for role configuration

3. **Prepare the default perimeter name**
   - Rename the default perimeter (id = 1) to match your main establishment (e.g. "Head Office")
   - This is the only perimeter that cannot be deleted

### Enable progressively

1. **Enable** the feature (Administration → Configuration → Perimeters)
2. **Rename** the default perimeter if needed
3. **Create** new perimeters for each establishment
4. **Move** existing objects to their correct perimeters
5. **Create** or update roles and assign users (see the *Roles* documentation)
6. **Test** with a pilot team before full rollout

### Moving objects safely

Before moving an object to another perimeter:

1. Check that the **object name does not already exist** in the target perimeter
2. Check that the **flows pointing to it** remain valid (flows adapt automatically)
3. Review the **audit trail** to understand the object's history
4. Notify the affected teams (especially if it affects their flows)

### Deleting a perimeter safely

1. **Audit** the perimeter's content
   - List all objects (Administration → Perimeters → [perimeter] → Objects)
   - List all roles (Administration → Perimeters → [perimeter] → Roles)

2. **Migrate the data**
   - Move all objects to another perimeter or to the default perimeter
   - Reassign all roles to another perimeter or to the default perimeter

3. **Delete** the perimeter
   - Administration → Configuration → Perimeters → Delete
   - Confirm (deletion is permanent)

4. **Verify** that the migration is complete
   - Check that no object or role still references the deleted perimeter

### When NOT to use perimeters

Perimeters are designed for **organizational separation** (several establishments, subsidiaries, business units). They are **not** suited for:

- **Temporary grouping** (use tags instead)
- **Team-level filtering** (use roles and permissions)
- **Object categorization** (use types, tags or a taxonomy)

If you do not have several autonomous establishments, leave perimeter management **disabled**. It adds complexity without any benefit.

## Troubleshooting

### "I enabled perimeter management but nothing changed"

✓ **Expected behavior.** All objects are still in the default perimeter (id = 1).  
✓ Create new perimeters and move objects to partition the cartography.  
✓ Assign roles to users in each perimeter (see the *Roles* documentation).  

### "I cannot move an object to another perimeter"

Possible reasons:

- You do not have a **write role** in the target perimeter (only read roles)
- The target perimeter already contains an object with the same name
- You are trying to move an object for which you are the **cartographer** (ask an administrator)

**Solution:** first create or request a role with write access in the target perimeter (see the *Roles* documentation).

### "A user cannot see objects they should see"

Check:

1. The **user's roles** (Administration → Users → [user] → Roles)
   - Check that the roles are assigned to the right perimeters
   - Check that the roles have read or write permissions (see the *Roles* documentation)

2. The **object's perimeter** (open the object, check the Perimeter field)
   - Check that the object is in one of the user's perimeters

3. The **cartographer assignments** (if the object is isolated)
   - Check whether the user is designated as a cartographer

### "A flow disappeared after I moved an object"

Flows are **not deleted** when you move an object. They are kept and remain visible to users of the perimeters concerned. If the flow seems to have disappeared:

1. Check that you still have a role in the perimeter the flow originates from
2. Reload the page (the cache may be outdated)
3. Check the audit log to see whether the flow was explicitly deleted

### "I deleted a perimeter and no longer have access to my objects"

This happens if:

- The objects were **not reassigned** before deletion (they have been lost)
- You still had a **role in that perimeter** (now deleted)

**Solution:**

- If the data is lost, restore from a backup
- If only the role is missing, ask an administrator to reassign you to another perimeter (see the *Roles* documentation)
