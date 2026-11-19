// Regenerates the «Flujos» page of the Planazo Figma file from the «App» page.
//
// How to run: execute this file's content with the Figma MCP `use_figma` tool
// (Plugin API, top-level await/return, no wrapper). File key: provided privately (not stored in the repo).
// Contract: resources/FLOWS.md §6 (flows and frames) and §11.6 (this procedure).
//
// What it does, per flow:
//   1. Clones the listed frames from «App» into a Section of the «Flujos» page, in row order.
//   2. Rewires the prototype reactions: a navigation survives only if its destination is
//      another frame of the same flow; everything else (navbar, other flows) is removed.
//   3. Frames listed in `terminal` lose every outgoing navigation, so the flow ends there.
//   4. Registers the flow's starting point on the «Flujos» page.
//
// Re-running replaces only the sections named in ONLY (empty = all flows). Never edit the
// «Flujos» page by hand: change «App» (or this config) and regenerate.

const ONLY = []; // e.g. ['TF1', 'TF4']; empty regenerates every flow
const GAP = 160; // horizontal space between frames
const PAD = 120; // section padding
const TITLE = 160; // room for the section title
const ROW_GAP = 240; // vertical space between sections

// Happy path only: each flow keeps just its own frames (no branches of other flows). Optional keys:
//   bridges: prototype-only links added on the copy (a button that hands over to another frame);
//   notes:   captions placed above a frame (e.g. "change of user");
//   alsoStart: extra Play entry points for role branches that cannot be reached from the main
// start (the app has no link between those frames). Each one is a distinct frame of the section.
// Keep in sync with resources/FLOWS.md §6 (catalog) and §11.1 (starting frames).
const FLOWS = [
    {
        tf: 'TF1',
        name: 'TF1 — Acceso y recuperar la contraseña',
        start: 'landing',
        frames: ['landing', 'ingresar', 'mis-planes', 'registro', 'mis-planes-vacio', 'recuperar', 'restablecer'],
        terminal: ['mis-planes', 'mis-planes-vacio'],
    },
    {
        tf: 'TF2',
        name: 'TF2 — Crear un plan y confirmarlo',
        start: 'mis-planes-vacio',
        frames: ['mis-planes-vacio', 'modal-crear', 'mis-planes', 'sala', 'modal-confirmar-plan', 'sala-confirmada'],
        terminal: ['sala-confirmada'],
    },
    {
        tf: 'TF3',
        name: 'TF3 — Invitar y responder una invitación',
        start: 'sala-sin-propuestas',
        frames: [
            'sala-sin-propuestas',
            'modal-invitar',
            'mis-planes-invitaciones',
            'sala-invitacion-pendiente',
            'sala-participante',
        ],
        terminal: ['sala-participante'],
        // Prototype-only link: sending the invitation hands the story over to the invited user.
        bridges: [{ from: 'modal-invitar', label: 'Enviar invitación', to: 'mis-planes-invitaciones' }],
        notes: [
            { frame: 'modal-invitar', text: 'Organizador: envía la invitación' },
            { frame: 'mis-planes-invitaciones', text: 'Cambio de usuario: el invitado abre su bandeja y acepta' },
        ],
    },
    {
        tf: 'TF4',
        name: 'TF4 — Proponer y votar',
        start: 'sala',
        frames: ['sala', 'modal-proponer-opcion', 'toast-alerta', 'sala-lugares', 'modal-proponer-lugar'],
        terminal: [],
    },
    {
        tf: 'TF5',
        name: 'TF5 — Registrar un gasto',
        start: 'sala-gastos',
        frames: ['sala-gastos', 'modal-crear-gasto', 'toast-alerta'],
        terminal: [],
    },
    {
        tf: 'TF6',
        name: 'TF6 — Saldar una deuda con el perfil de pago',
        start: 'sala-participante-gastos',
        frames: [
            'sala-participante-gastos',
            'modal-configurar-cobro',
            'modal-crear-pago-participante',
            'modal-crear-pago-totalidad-participante',
            'modal-crear-pago-parcial-participante',
            'toast-alerta',
        ],
        terminal: [],
    },
    {
        tf: 'TF7',
        name: 'TF7 — Salir de un plan',
        start: 'sala-participante',
        frames: ['sala-participante', 'modal-retirarse-sin-deuda', 'mis-planes'],
        terminal: ['mis-planes'],
    },
    {
        tf: 'TF8',
        name: 'TF8 — Eliminar mi cuenta o descargar mis datos',
        start: 'mi-cuenta',
        frames: ['mi-cuenta', 'toast-alerta', 'modal-bloqueos-cuenta', 'sala', 'modal-eliminar-cuenta', 'ingresar'],
        terminal: ['sala', 'ingresar'],
    },
];

// ---------------------------------------------------------------------------------------
const app = figma.root.children.find((p) => p.name === 'App');
if (!app) throw new Error('Page «App» not found');
let flujos = figma.root.children.find((p) => p.name === 'Flujos');
if (!flujos) {
    flujos = figma.createPage();
    flujos.name = 'Flujos';
}
await app.loadAsync();
await flujos.loadAsync();

const originals = new Map();
for (const c of app.children) if (c.type === 'FRAME' && !originals.has(c.name)) originals.set(c.name, c);

// Frame-to-frame navigation. CHANGE_TO is a component-variant swap (hover states): never touched.
const isNav = (a) => a.type === 'NODE' && a.destinationId && a.navigation !== 'CHANGE_TO';

// Keeps an action list: drops navigations outside the flow (or all of them on terminal frames).
function remap(actions, map, strip) {
    const out = [];
    for (const a of actions) {
        if (isNav(a)) {
            const d = strip ? null : map.get(a.destinationId);
            if (d) out.push({ ...a, destinationId: d });
        } else if (a.type === 'CONDITIONAL') {
            const blocks = a.conditionalBlocks.map((b) => ({ ...b, actions: remap(b.actions || [], map, strip) }));
            out.push({ ...a, conditionalBlocks: blocks });
        } else {
            out.push(a);
        }
    }
    return out;
}

// Nodes with reactions, including the ones inside nested instances (the navbar): findAll
// called on a frame does not always reach them, so every instance is searched on its own.
function nodesWithReactions(root) {
    const has = (n) => 'reactions' in n && n.reactions && n.reactions.length > 0;
    const found = new Map();
    const add = (n) => {
        if (has(n)) found.set(n.id, n);
    };
    add(root);
    root.findAll(has).forEach(add);
    for (const inst of root.findAll((n) => n.type === 'INSTANCE')) {
        add(inst);
        inst.findAll(has).forEach(add);
    }
    return [...found.values()];
}

// Index path of `node` from `root` (used to find the twin node in the clone).
function pathOf(node, root) {
    const path = [];
    for (let n = node; n.id !== root.id; n = n.parent) path.unshift(n.parent.children.findIndex((x) => x.id === n.id));
    return path;
}

// clone() silently drops every reaction whose destination is outside the cloned subtree, so the
// reactions are read from the ORIGINAL frame and re-applied (remapped) on the twin node.
async function rewire(origRoot, cloneRoot, map, strip) {
    for (const n of nodesWithReactions(origRoot)) {
        let twin = cloneRoot;
        for (const i of pathOf(n, origRoot)) twin = twin.children[i];
        const reactions = [];
        for (const r of n.reactions) {
            const actions = remap(r.actions || (r.action ? [r.action] : []), map, strip);
            // Do not spread `r`: it also carries the deprecated `action`, which would win over `actions`.
            if (actions.length) reactions.push({ trigger: r.trigger, actions });
        }
        await twin.setReactionsAsync(reactions);
    }
}

// Text nodes of a frame, including those inside nested instances.
function textNodes(root) {
    const out = new Map();
    const add = (n) => out.set(n.id, n);
    root.findAll((n) => n.type === 'TEXT').forEach(add);
    for (const inst of root.findAll((n) => n.type === 'INSTANCE')) inst.findAll((n) => n.type === 'TEXT').forEach(add);
    return [...out.values()];
}

// Prototype-only link: the button labelled `label` inside frame `from` navigates to `to`
// (replaces its click action, keeps hover states).
async function applyBridge(clones, b) {
    const from = clones.get(b.from);
    const to = clones.get(b.to);
    const text = textNodes(from).find((t) => t.characters.trim() === b.label);
    let host = text;
    while (
        host &&
        host.id !== from.id &&
        !(host.reactions && host.reactions.some((r) => r.trigger && r.trigger.type === 'ON_CLICK'))
    )
        host = host.parent;
    if (!host || host.id === from.id) return `bridge ${b.from} «${b.label}»: no clickable ancestor`;
    const kept = host.reactions
        .filter((r) => !(r.trigger && r.trigger.type === 'ON_CLICK'))
        .map((r) => ({ trigger: r.trigger, actions: r.actions }));
    const click = {
        trigger: { type: 'ON_CLICK' },
        actions: [
            { type: 'NODE', destinationId: to.id, navigation: 'NAVIGATE', transition: null, resetVideoPosition: false },
        ],
    };
    await host.setReactionsAsync([...kept, click]);
    return null;
}

// Caption above a frame, inside the section's title band.
async function addNote(section, frame, text) {
    await figma.loadFontAsync({ family: 'Inter', style: 'Bold' });
    const t = figma.createText();
    t.fontName = { family: 'Inter', style: 'Bold' };
    t.fontSize = 40;
    t.characters = text;
    t.fills = [{ type: 'SOLID', color: { r: 0.15, g: 0.15, b: 0.15 } }];
    section.appendChild(t);
    t.x = frame.x;
    t.y = 50;
    t.name = 'Nota · ' + text;
}

const report = {};
const warnings = [];

// Remove sections of flows that no longer exist (renamed or deleted in FLOWS) and their entry points.
const validNames = new Set(FLOWS.map((f) => f.name));
for (const c of [...flujos.children]) if (c.type === 'SECTION' && !validNames.has(c.name)) c.remove();
const startingPoints = [];
for (const sp of flujos.flowStartingPoints) {
    if (await figma.getNodeByIdAsync(sp.nodeId)) startingPoints.push(sp);
}

for (const flow of FLOWS) {
    if (ONLY.length && !ONLY.includes(flow.tf)) continue;

    const missing = flow.frames.filter((n) => !originals.has(n));
    if (missing.length) {
        report[flow.tf] = { error: 'missing frames in App', missing };
        continue;
    }

    // Reuse position of an existing section; otherwise stack below the others.
    const previous = flujos.children.find((c) => c.type === 'SECTION' && c.name === flow.name);
    let x = 0;
    let y = 0;
    if (previous) {
        x = previous.x;
        y = previous.y;
        previous.remove();
    } else {
        const bottoms = flujos.children.map((c) => c.y + c.height);
        y = bottoms.length ? Math.max(...bottoms) + ROW_GAP : 0;
    }

    const section = figma.createSection();
    section.name = flow.name;
    flujos.appendChild(section);
    section.x = x;
    section.y = y;

    // 1. Clone frames in order.
    const map = new Map(); // original id -> clone id
    const clones = new Map(); // name -> clone
    let cursor = PAD;
    let maxH = 0;
    for (const name of flow.frames) {
        const o = originals.get(name);
        const c = o.clone();
        section.appendChild(c);
        c.x = cursor;
        c.y = TITLE;
        cursor += c.width + GAP;
        maxH = Math.max(maxH, c.height);
        map.set(o.id, c.id);
        clones.set(name, c);
    }
    section.resizeWithoutConstraints(cursor - GAP + PAD, TITLE + maxH + PAD);

    // 2. Rewire reactions inside the flow only.
    for (const [name, c] of clones) await rewire(originals.get(name), c, map, flow.terminal.includes(name));

    // 2b. Prototype-only bridges and captions.
    for (const b of flow.bridges || []) {
        const w = await applyBridge(clones, b);
        if (w) warnings.push(w);
    }
    for (const n of flow.notes || []) await addNote(section, clones.get(n.frame), n.text);

    // 3. Starting point.
    const startClone = clones.get(flow.start);
    const i = startingPoints.findIndex((s) => s.name === flow.name);
    if (i >= 0) startingPoints.splice(i, 1);
    startingPoints.push({ nodeId: startClone.id, name: flow.name });
    for (const extra of flow.alsoStart || []) {
        const j = startingPoints.findIndex((s) => s.name === extra.name);
        if (j >= 0) startingPoints.splice(j, 1);
        startingPoints.push({ nodeId: clones.get(extra.frame).id, name: extra.name });
    }

    // 4. Reachability from the start (frames only reached via other flows stay as captures).
    const edges = new Map();
    const cloneIds = new Set([...clones.values()].map((c) => c.id));
    let stray = 0;
    for (const [, c] of clones) {
        const dest = new Set();
        for (const n of nodesWithReactions(c)) {
            for (const r of n.reactions)
                for (const a of r.actions || [])
                    if (isNav(a)) {
                        dest.add(a.destinationId);
                        if (!cloneIds.has(a.destinationId)) stray++;
                    }
        }
        edges.set(c.id, dest);
    }
    const seeds = [startClone.id, ...(flow.alsoStart || []).map((e) => clones.get(e.frame).id)];
    const seen = new Set(seeds);
    const queue = [...seeds];
    while (queue.length) {
        const id = queue.shift();
        for (const d of edges.get(id) || [])
            if (!seen.has(d)) {
                seen.add(d);
                queue.push(d);
            }
    }
    report[flow.tf] = {
        section: section.id,
        frames: flow.frames.length,
        strayLinks: stray, // navigations pointing outside the flow: must be 0
        unreachableFromStart: flow.frames.filter((n) => !seen.has(clones.get(n).id)),
    };
}

// Stack the sections in flow order (TF1 on top) so the page reads like the catalog.
let stackY = 0;
for (const flow of FLOWS) {
    const sec = flujos.children.find((c) => c.type === 'SECTION' && c.name === flow.name);
    if (!sec) continue;
    sec.x = 0;
    sec.y = stackY;
    stackY += sec.height + ROW_GAP;
}

const rank = (n) => FLOWS.findIndex((f) => n.startsWith(f.tf));
// Sections regenerated above removed their old frames: drop entry points that no longer resolve.
const alive = [];
for (const sp of startingPoints) if (await figma.getNodeByIdAsync(sp.nodeId)) alive.push(sp);
flujos.flowStartingPoints = alive.sort(
    (a, b) =>
        rank(a.name) - rank(b.name) ||
        b.name.includes(' — ') - a.name.includes(' — ') ||
        a.name.localeCompare(b.name, 'es')
);
return { report, warnings };
