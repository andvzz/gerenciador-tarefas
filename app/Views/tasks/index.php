<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$colunas = [
    ['key' => 'pendente',     'label' => 'A FAZER',       'dot' => '#7EAEE7'],
    ['key' => 'em andamento', 'label' => 'EM ANDAMENTO',  'dot' => '#0000FF'],
    ['key' => 'concluída',    'label' => 'CONCLUÍDO',     'dot' => '#22C55E'],
];

$agrupadas = ['pendente' => [], 'em andamento' => [], 'concluída' => []];
foreach ($tarefas as $tarefa) {
    if (isset($agrupadas[$tarefa['status']])) {
        $agrupadas[$tarefa['status']][] = $tarefa;
    }
}
?>

<style>
    /* Kanban */
    .kb-board { display: flex; gap: 22px; align-items: flex-start; overflow-x: auto; padding-bottom: 16px; }

    .kb-col { width: 340px; flex: 0 0 340px; border-radius: 26px; overflow: hidden; }

    .kb-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 18px 14px; border-bottom: 1px solid rgba(0, 0, 255, 0.10);
    }
    .kb-head .left { display: flex; align-items: center; gap: 10px; }
    .kb-dot { width: 9px; height: 9px; border-radius: 9999px; box-shadow: 0 0 8px currentColor; }
    .kb-title { font-size: 12px; font-weight: 900; letter-spacing: 0.15em; text-transform: uppercase; color: #0B2A4A; }
    .kb-count {
        font-size: 11px; font-weight: 900; letter-spacing: 0.05em;
        padding: 4px 11px; border-radius: 9999px;
        background: #EAF2FB; border: 1px solid #A9C9EF; color: #0000FF;
        font-variant-numeric: tabular-nums;
    }

    .kb-list { min-height: 460px; padding: 16px; display: flex; flex-direction: column; gap: 14px; }

    .kb-empty {
        flex: 1; min-height: 200px;
        display: flex; align-items: center; justify-content: center; text-align: center;
        border: 2px dashed #A9C9EF; border-radius: 20px;
        font-size: 11px; font-weight: 900; letter-spacing: 0.2em; text-transform: uppercase;
        color: rgba(11, 42, 74, 0.30);
    }

    .kb-card {
        position: relative; background: #FFFFFF;
        border: 1px solid rgba(0, 0, 255, 0.12); border-radius: 20px;
        padding: 16px 16px 14px; cursor: grab; overflow: hidden;
        box-shadow: 0 10px 24px -16px rgba(11, 42, 74, 0.30);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .kb-card::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #5393DF, #0000FF, #073F7C);
        opacity: .55; transition: opacity .2s ease;
    }
    .kb-card:hover { transform: translateY(-2px); border-color: rgba(0, 0, 255, 0.45); box-shadow: 0 18px 36px -18px rgba(11, 42, 74, 0.45); }
    .kb-card:hover::before { opacity: 1; }
    .kb-card:active { cursor: grabbing; }

    .kb-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .kb-card-title { font-size: 14px; font-weight: 700; line-height: 1.4; color: #0B2A4A; word-break: break-word; }
    .kb-card-desc {
        margin: 10px 0 0; font-size: 12px; line-height: 1.5; color: rgba(11, 42, 74, 0.55);
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .kb-card-foot {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(0, 0, 255, 0.10);
    }
    .kb-date { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: rgba(11, 42, 74, 0.45); }

    .kb-actions { display: flex; gap: 6px; opacity: 0; transition: opacity .2s ease; }
    .kb-card:hover .kb-actions { opacity: 1; }
    .kb-action {
        display: flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 10px;
        text-decoration: none; transition: all .2s ease;
    }
    .kb-action.edit { background: #EAF2FB; color: #0000FF; }
    .kb-action.edit:hover { background: #0000FF; color: #fff; }
    .kb-action.del { background: #FEF2F2; color: #EF4444; }
    .kb-action.del:hover { background: #EF4444; color: #fff; }

    .kb-ghost { opacity: .4; }
    .kb-drag  { transform: rotate(2deg); }

    #kb-toast {
        position: fixed; bottom: 26px; left: 50%; transform: translateX(-50%) translateY(20px);
        padding: 12px 22px; border-radius: 14px; font-size: 13px; font-weight: 700;
        background: #fff; border: 1px solid #A9C9EF; color: #0B2A4A;
        box-shadow: 0 20px 40px -14px rgba(11, 42, 74, 0.35);
        opacity: 0; pointer-events: none; transition: all .3s ease; z-index: 100;
    }
    #kb-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    #kb-toast.error { border-color: #FECACA; color: #DC2626; }

    /* Modal */
    .kb-modal {
        position: fixed; inset: 0; z-index: 200;
        display: none; align-items: center; justify-content: center;
        padding: 20px;
    }
    .kb-modal.open { display: flex; }
    .kb-modal-overlay {
        position: absolute; inset: 0;
        background: rgba(11, 42, 74, 0.45); backdrop-filter: blur(4px);
        opacity: 0; transition: opacity .25s ease;
    }
    .kb-modal.open .kb-modal-overlay { opacity: 1; }
    .kb-modal-panel {
        position: relative; width: 100%; max-width: 560px;
        background: #FFFFFF; border: 1px solid rgba(0, 0, 255, 0.12);
        border-radius: 28px;
        box-shadow: 0 40px 80px -24px rgba(11, 42, 74, 0.55);
        transform: translateY(16px) scale(.97); opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
        max-height: 92vh; overflow: hidden;
    }
    .kb-modal-body {
        padding: 28px 28px 26px;
        max-height: 92vh; overflow-y: auto;
    }
    .kb-modal.open .kb-modal-panel { transform: translateY(0) scale(1); opacity: 1; }
    .kb-modal-close {
        position: absolute; top: 18px; right: 18px;
        display: flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 12px; border: none;
        background: #EAF2FB; color: #0000FF; cursor: pointer; transition: all .2s ease;
    }
    .kb-modal-close:hover { background: #0000FF; color: #fff; }

    /* Select de status */
    .kb-select { position: relative; }

    .kb-select-trigger {
        display: flex; align-items: center; gap: 10px; width: 100%;
        padding: 12px 16px; border-radius: 12px;
        border: 1px solid rgba(0, 0, 255, 0.15); background: rgba(255, 255, 255, 0.70);
        font-size: 14px; font-weight: 600; color: #0B2A4A; text-align: left;
        cursor: pointer; outline: none; transition: border-color .2s ease, box-shadow .2s ease;
    }
    .kb-select-trigger:hover { border-color: rgba(0, 0, 255, 0.35); }
    .kb-select[data-open="true"] .kb-select-trigger {
        border-color: #5393DF; box-shadow: 0 0 0 2px rgba(83, 147, 223, 0.30);
    }

    .kb-select-value { flex: 1; }
    .kb-select-dot { width: 9px; height: 9px; border-radius: 9999px; flex: 0 0 auto; box-shadow: 0 0 6px currentColor; }
    .kb-select-chevron { flex: 0 0 auto; color: rgba(11, 42, 74, 0.45); transition: transform .2s ease; }
    .kb-select[data-open="true"] .kb-select-chevron { transform: rotate(180deg); }

    .kb-select-list {
        position: absolute; top: calc(100% + 8px); left: 0; right: 0; z-index: 50;
        margin: 0; padding: 6px; list-style: none;
        background: #FFFFFF; border: 1px solid rgba(0, 0, 255, 0.12); border-radius: 16px;
        box-shadow: 0 24px 48px -18px rgba(11, 42, 74, 0.45);
        opacity: 0; transform: translateY(-6px); pointer-events: none;
        transition: opacity .18s ease, transform .18s ease;
    }
    .kb-select[data-open="true"] .kb-select-list {
        opacity: 1; transform: translateY(0); pointer-events: auto;
    }

    .kb-select-option {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 11px;
        font-size: 14px; font-weight: 600; color: #0B2A4A;
        cursor: pointer; transition: background-color .15s ease, color .15s ease;
    }
    .kb-select-option > span:nth-child(2) { flex: 1; }
    .kb-select-option:hover { background: #EAF2FB; }
    .kb-select-option[aria-selected="true"] { background: #EAF2FB; color: #0000FF; }

    .kb-select-check { flex: 0 0 auto; color: #0000FF; opacity: 0; transition: opacity .15s ease; }
    .kb-select-option[aria-selected="true"] .kb-select-check { opacity: 1; }
</style>

<div class="flex flex-col md:flex-row md:items-end md:justify-start gap-6 mb-10">
    <button type="button" id="btn-nova-tarefa"
       class="group inline-flex items-center justify-center gap-3 px-6 py-3.5 bg-brand-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-xl shadow-brand-600/30 hover:bg-brand-700 hover:-translate-y-0.5 active:scale-95 transition-all">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:rotate-90 transition-transform duration-300"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Nova Tarefa</span>
    </button>
</div>

<div class="kb-board">
    <?php foreach ($colunas as $coluna): ?>
        <?php $tarefasColuna = $agrupadas[$coluna['key']]; ?>
        <div class="glass kb-col">
            <div class="kb-head">
                <div class="left">
                    <span class="kb-dot" style="background: <?= $coluna['dot'] ?>; color: <?= $coluna['dot'] ?>;"></span>
                    <span class="kb-title"><?= esc($coluna['label']) ?></span>
                </div>
                <span class="kb-count"><?= str_pad((string) count($tarefasColuna), 2, '0', STR_PAD_LEFT) ?></span>
            </div>

            <div class="kb-list" data-status="<?= esc($coluna['key'], 'attr') ?>">
                <?php foreach ($tarefasColuna as $tarefa): ?>
                    <div class="kb-card" data-id="<?= esc($tarefa['id'], 'attr') ?>"
                         data-title="<?= esc($tarefa['title'], 'attr') ?>"
                         data-description="<?= esc($tarefa['description'] ?? '', 'attr') ?>">
                        <div class="kb-card-top">
                            <span class="kb-card-title"><?= esc($tarefa['title']) ?></span>
                            <div class="kb-actions">
                                <button type="button" class="kb-action edit" title="Editar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <a class="kb-action del" href="<?= site_url('tasks/delete/' . $tarefa['id']) ?>" title="Excluir" onclick="return confirm('Deseja realmente excluir esta tarefa?');">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </a>
                            </div>
                        </div>
                        <?php if (! empty($tarefa['description'])): ?>
                            <p class="kb-card-desc"><?= esc($tarefa['description']) ?></p>
                        <?php endif; ?>
                        <div class="kb-card-foot">
                            <span class="kb-date">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <?= esc(date('d/m/Y', strtotime($tarefa['created_at']))) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="kb-empty" style="display: <?= empty($tarefasColuna) ? 'flex' : 'none' ?>;">Mova itens para cá</div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->include('tasks/create') ?>
<?= $this->include('tasks/edit') ?>

<div id="kb-toast"></div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    const CSRF_NAME  = '<?= csrf_token() ?>';
    let   CSRF_HASH  = '<?= csrf_hash() ?>';
    const UPDATE_URL = '<?= site_url('tasks/update-status') ?>';
    const STORE_URL  = '<?= site_url('tasks/store') ?>';
    const UPDATE_BASE = '<?= site_url('tasks/update') ?>';
    const DELETE_BASE = '<?= site_url('tasks/delete') ?>';

    const SVG_EDIT = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
    const SVG_DEL  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
    const SVG_CAL  = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';

    /* ---------------- Toast ---------------- */
    const toastEl = document.getElementById('kb-toast');
    let toastTimer;
    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.classList.toggle('error', isError);
        toastEl.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toastEl.classList.remove('show'), 2600);
    }

    /* ---------------- Colunas / cards ---------------- */
    function refreshColumns() {
        document.querySelectorAll('.kb-col').forEach(col => {
            const list  = col.querySelector('.kb-list');
            const count = list.querySelectorAll('.kb-card').length;
            col.querySelector('.kb-count').textContent = String(count).padStart(2, '0');
            const empty = list.querySelector('.kb-empty');
            if (empty) empty.style.display = count === 0 ? 'flex' : 'none';
        });
    }

    function fmtDate(raw) {
        if (!raw) return '';
        const parts = String(raw).slice(0, 10).split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : raw;
    }

    function buildCard(task) {
        const card = document.createElement('div');
        card.className = 'kb-card';
        card.dataset.id          = task.id;
        card.dataset.title       = task.title || '';
        card.dataset.description = task.description || '';

        const top = document.createElement('div');
        top.className = 'kb-card-top';

        const titleSpan = document.createElement('span');
        titleSpan.className = 'kb-card-title';
        titleSpan.textContent = task.title || '';

        const actions = document.createElement('div');
        actions.className = 'kb-actions';

        const editBtn = document.createElement('button');
        editBtn.type = 'button';
        editBtn.className = 'kb-action edit';
        editBtn.title = 'Editar';
        editBtn.innerHTML = SVG_EDIT;

        const delLink = document.createElement('a');
        delLink.className = 'kb-action del';
        delLink.href = `${DELETE_BASE}/${task.id}`;
        delLink.title = 'Excluir';
        delLink.setAttribute('onclick', "return confirm('Deseja realmente excluir esta tarefa?');");
        delLink.innerHTML = SVG_DEL;

        actions.append(editBtn, delLink);
        top.append(titleSpan, actions);
        card.append(top);

        if (task.description) {
            const desc = document.createElement('p');
            desc.className = 'kb-card-desc';
            desc.textContent = task.description;
            card.append(desc);
        }

        const foot = document.createElement('div');
        foot.className = 'kb-card-foot';
        const date = document.createElement('span');
        date.className = 'kb-date';
        date.innerHTML = SVG_CAL;
        date.append(document.createTextNode(' ' + fmtDate(task.created_at)));
        foot.append(date);
        card.append(foot);

        return card;
    }

    function listFor(status) {
        return document.querySelector(`.kb-list[data-status="${status}"]`);
    }

    function placeCard(task, removeId) {
        if (removeId) {
            const old = document.querySelector(`.kb-card[data-id="${removeId}"]`);
            if (old) old.remove();
        }
        const list = listFor(task.status);
        if (!list) return;
        const card = buildCard(task);
        list.insertBefore(card, list.querySelector('.kb-empty') || null);
    }

    /* ---------------- Drag & drop ---------------- */
    async function persistStatus(card, toList, fromList, oldIndex) {
        const body = new URLSearchParams();
        body.append('id', card.dataset.id);
        body.append('status', toList.dataset.status);
        body.append(CSRF_NAME, CSRF_HASH);

        try {
            const res  = await fetch(UPDATE_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body
            });
            const data = await res.json();

            if (data && data.csrf) CSRF_HASH = data.csrf;

            if (!res.ok || !data.success) throw new Error((data && data.error) || 'Falha ao atualizar.');

            showToast('Status atualizado com sucesso!');
        } catch (err) {
            const ref = fromList.querySelectorAll('.kb-card')[oldIndex] || fromList.querySelector('.kb-empty');
            fromList.insertBefore(card, ref || null);
            refreshColumns();
            showToast('Erro ao atualizar. Alteração revertida.', true);
        }
    }

    document.querySelectorAll('.kb-list').forEach(list => {
        new Sortable(list, {
            group: 'kanban',
            animation: 160,
            draggable: '.kb-card',
            filter: '.kb-action',
            preventOnFilter: false,
            ghostClass: 'kb-ghost',
            dragClass: 'kb-drag',
            onEnd: (evt) => {
                refreshColumns();
                if (evt.to === evt.from) return;
                persistStatus(evt.item, evt.to, evt.from, evt.oldIndex);
            }
        });
    });

    refreshColumns();

    /* ---------------- Status select (widget reutilizável) ---------------- */
    function createStatusSelect(prefix) {
        const wrap    = document.getElementById(`${prefix}-status-select`);
        const trigger = document.getElementById(`${prefix}-status-trigger`);
        const list    = document.getElementById(`${prefix}-status-list`);
        const dot     = document.getElementById(`${prefix}-status-dot`);
        const valueEl = document.getElementById(`${prefix}-status-value`);
        const hidden  = document.getElementById(`${prefix}-status`);
        const options = Array.from(list.querySelectorAll('.kb-select-option'));

        function setStatus(value) {
            const opt = options.find(o => o.dataset.value === value) || options[0];
            hidden.value = opt.dataset.value;
            valueEl.textContent = opt.querySelector('span:nth-child(2)').textContent;
            dot.style.background = opt.querySelector('.kb-select-dot').style.background;
            options.forEach(o => o.setAttribute('aria-selected', o === opt ? 'true' : 'false'));
        }
        const open  = () => { wrap.dataset.open = 'true';  trigger.setAttribute('aria-expanded', 'true'); };
        const close = () => { wrap.dataset.open = 'false'; trigger.setAttribute('aria-expanded', 'false'); };
        const toggle = () => { wrap.dataset.open === 'true' ? close() : open(); };

        trigger.addEventListener('click', (e) => { e.stopPropagation(); toggle(); });
        options.forEach(opt => opt.addEventListener('click', () => { setStatus(opt.dataset.value); close(); }));
        document.addEventListener('click', (e) => {
            if (wrap.dataset.open === 'true' && !wrap.contains(e.target)) close();
        });

        return { setStatus, open, close, getValue: () => hidden.value, isOpen: () => wrap.dataset.open === 'true' };
    }

    /* ---------------- Modal (controlador reutilizável) ---------------- */
    function createModalController(prefix) {
        const modal      = document.getElementById(`${prefix}-modal`);
        const form       = document.getElementById(`${prefix}-form`);
        const titleInput = document.getElementById(`${prefix}-title`);
        const descInput  = document.getElementById(`${prefix}-description`);
        const submitBtn  = document.getElementById(`${prefix}-form-submit`);
        const errorsBox  = document.getElementById(`${prefix}-form-errors`);
        const errorsList = errorsBox.querySelector('ul');
        const status     = createStatusSelect(prefix);

        function clearErrors() { errorsBox.classList.add('hidden'); errorsList.innerHTML = ''; }
        function showErrors(errors) {
            errorsList.innerHTML = '';
            Object.values(errors).forEach(msg => {
                const li = document.createElement('li');
                li.textContent = msg;
                errorsList.append(li);
            });
            errorsBox.classList.remove('hidden');
        }
        function open() {
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            setTimeout(() => titleInput.focus(), 60);
        }
        function close() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            status.close();
            clearErrors();
        }

        modal.querySelectorAll('[data-modal-close]').forEach(el => el.addEventListener('click', close));

        return { modal, form, titleInput, descInput, submitBtn, status, clearErrors, showErrors, open, close, isOpen: () => modal.classList.contains('open') };
    }

    const createCtl = createModalController('create');
    const editCtl   = createModalController('edit');
    let   editId    = null;

    /* ---------------- Submit (AJAX) ---------------- */
    function handleSubmit(ctl, getUrl, isEdit) {
        ctl.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            ctl.clearErrors();

            const url  = getUrl();
            const body = new URLSearchParams();
            body.append('title', ctl.titleInput.value);
            body.append('description', ctl.descInput.value);
            body.append('status', ctl.status.getValue());
            body.append(CSRF_NAME, CSRF_HASH);

            ctl.submitBtn.disabled = true;
            try {
                const res  = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body
                });
                const data = await res.json();

                if (data && data.csrf) CSRF_HASH = data.csrf;

                if (res.status === 400 && data.errors) {
                    ctl.showErrors(data.errors);
                    return;
                }
                if (!res.ok || data.status !== 'success') {
                    throw new Error((data && data.message) || 'Falha ao salvar a tarefa.');
                }

                placeCard(data.task, isEdit ? data.task.id : null);
                refreshColumns();
                ctl.close();
                showToast(data.message || 'Tarefa salva com sucesso!');
            } catch (err) {
                showToast(err.message || 'Erro ao salvar a tarefa.', true);
            } finally {
                ctl.submitBtn.disabled = false;
            }
        });
    }

    handleSubmit(createCtl, () => STORE_URL, false);
    handleSubmit(editCtl, () => `${UPDATE_BASE}/${editId}`, true);

    /* ---------------- Abrir modais ---------------- */
    document.getElementById('btn-nova-tarefa').addEventListener('click', () => {
        createCtl.form.reset();
        createCtl.clearErrors();
        createCtl.status.setStatus('pendente');
        createCtl.open();
    });

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.kb-action.edit');
        if (!editBtn) return;
        const card = editBtn.closest('.kb-card');
        if (!card) return;

        editCtl.form.reset();
        editCtl.clearErrors();
        editId = card.dataset.id;
        editCtl.titleInput.value = card.dataset.title || '';
        editCtl.descInput.value  = card.dataset.description || '';
        editCtl.status.setStatus(card.closest('.kb-list').dataset.status);
        document.getElementById('edit-modal-subtitle').textContent = `Atualizando a tarefa #${editId}.`;
        editCtl.open();
    });

    /* ---------------- ESC ---------------- */
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (createCtl.status.isOpen() || editCtl.status.isOpen()) {
            createCtl.status.close();
            editCtl.status.close();
            return;
        }
        if (createCtl.isOpen()) createCtl.close();
        if (editCtl.isOpen()) editCtl.close();
    });
</script>

<?= $this->endSection() ?>
