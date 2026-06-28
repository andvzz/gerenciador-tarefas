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
        border-radius: 28px; padding: 28px 28px 26px;
        box-shadow: 0 40px 80px -24px rgba(11, 42, 74, 0.55);
        transform: translateY(16px) scale(.97); opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
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

<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
    <div class="space-y-2">
        <h1 class="text-4xl md:text-5xl font-black tracking-tighter leading-none uppercase text-ink">
            Quadro de <span class="text-brand-600">Tarefas</span>
        </h1>
        <p class="text-sm font-medium text-brand-900/50">
            Arraste os cards entre as colunas para atualizar o status.
        </p>
    </div>

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
                                <a class="kb-action del" href="<?= site_url('tarefas/excluir/' . $tarefa['id']) ?>" title="Excluir" onclick="return confirm('Deseja realmente excluir esta tarefa?');">
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

<div id="task-modal" class="kb-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="task-modal-title">
    <div class="kb-modal-overlay" data-modal-close></div>

    <div class="kb-modal-panel">
        <button type="button" class="kb-modal-close" data-modal-close title="Fechar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div class="space-y-1 mb-7 pr-10">
            <h2 id="task-modal-title" class="text-2xl md:text-3xl font-black tracking-tighter uppercase text-ink">
                Nova <span class="text-brand-600">Tarefa</span>
            </h2>
            <p id="task-modal-subtitle" class="text-sm font-medium text-brand-900/50">Crie uma nova atividade para o projeto.</p>
        </div>

        <div id="task-form-errors" class="mb-6 rounded-2xl border border-red-200 bg-red-50/80 px-5 py-4 hidden">
            <p class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-red-600 mb-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Corrija os erros abaixo
            </p>
            <ul class="list-disc list-inside space-y-1 text-sm font-medium text-red-700"></ul>
        </div>

        <form id="task-form" class="flex flex-col gap-6" data-mode="create" data-id="">
            <?= csrf_field() ?>

            <div class="flex flex-col gap-2">
                <label for="task-title" class="text-[11px] font-black uppercase tracking-[0.15em] text-brand-900/60 px-1">Título</label>
                <input type="text" id="task-title" name="title" required
                       placeholder="Ex: Revisar proposta do cliente"
                       class="w-full rounded-xl border border-brand-600/15 bg-white/70 px-4 py-3 text-sm font-medium text-ink placeholder:text-brand-900/30 outline-none transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="task-description" class="text-[11px] font-black uppercase tracking-[0.15em] text-brand-900/60 px-1">Descrição <span class="text-brand-900/30 normal-case tracking-normal font-semibold">(opcional)</span></label>
                <textarea id="task-description" name="description" rows="4"
                          placeholder="Adicione detalhes, links ou instruções..."
                          class="w-full rounded-xl border border-brand-600/15 bg-white/70 px-4 py-3 text-sm font-medium text-ink placeholder:text-brand-900/30 outline-none transition-all resize-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"></textarea>
            </div>

            <div class="flex flex-col gap-2">
                <span id="task-status-label" class="text-[11px] font-black uppercase tracking-[0.15em] text-brand-900/60 px-1">Status</span>

                <?php
                $coresStatus = [
                    'pendente'     => '#7EAEE7',
                    'em andamento' => '#0000FF',
                    'concluída'    => '#22C55E',
                ];
                ?>

                <input type="hidden" id="task-status" name="status" value="pendente">

                <div id="task-status-select" class="kb-select" data-open="false">
                    <button type="button" id="task-status-trigger" class="kb-select-trigger"
                            aria-haspopup="listbox" aria-expanded="false" aria-labelledby="task-status-label">
                        <span id="task-status-dot" class="kb-select-dot" style="background: <?= $coresStatus['pendente'] ?>;"></span>
                        <span id="task-status-value" class="kb-select-value">Pendente</span>
                        <svg class="kb-select-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    <ul id="task-status-list" class="kb-select-list" role="listbox" aria-labelledby="task-status-label">
                        <?php foreach ($coresStatus as $valor => $cor): ?>
                            <li class="kb-select-option" role="option" data-value="<?= esc($valor, 'attr') ?>"
                                aria-selected="<?= $valor === 'pendente' ? 'true' : 'false' ?>">
                                <span class="kb-select-dot" style="background: <?= $cor ?>;"></span>
                                <span><?= ucfirst($valor) ?></span>
                                <svg class="kb-select-check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" data-modal-close
                        class="flex-1 text-center py-3 px-4 rounded-xl border border-brand-600/15 text-sm font-bold text-brand-900/70 hover:bg-brand-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="task-form-submit"
                        class="flex-[2] inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-brand-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-brand-600/30 hover:bg-brand-700 hover:scale-[1.01] active:scale-95 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span id="task-form-submit-label">Salvar Tarefa</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="kb-toast"></div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    const CSRF_NAME = '<?= csrf_token() ?>';
    let   CSRF_HASH = '<?= csrf_hash() ?>';
    const UPDATE_URL = '<?= site_url('tarefas/atualizar-status') ?>';

    const toastEl = document.getElementById('kb-toast');
    let toastTimer;
    function showToast(msg, isError = false) {
        toastEl.textContent = msg;
        toastEl.classList.toggle('error', isError);
        toastEl.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toastEl.classList.remove('show'), 2600);
    }

    function refreshColumns() {
        document.querySelectorAll('.kb-col').forEach(col => {
            const list  = col.querySelector('.kb-list');
            const count = list.querySelectorAll('.kb-card').length;
            col.querySelector('.kb-count').textContent = String(count).padStart(2, '0');
            const empty = list.querySelector('.kb-empty');
            if (empty) empty.style.display = count === 0 ? 'flex' : 'none';
        });
    }

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

    const STORE_URL   = '<?= site_url('tarefas/salvar') ?>';
    const UPDATE_BASE = '<?= site_url('tarefas/atualizar') ?>';
    const DELETE_BASE = '<?= site_url('tarefas/excluir') ?>';

    const SVG_EDIT = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
    const SVG_DEL  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
    const SVG_CAL  = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';

    const modal         = document.getElementById('task-modal');
    const form          = document.getElementById('task-form');
    const titleInput    = document.getElementById('task-title');
    const descInput     = document.getElementById('task-description');
    const statusSelect  = document.getElementById('task-status');
    const modalTitle    = document.getElementById('task-modal-title');
    const modalSubtitle = document.getElementById('task-modal-subtitle');
    const submitBtn     = document.getElementById('task-form-submit');
    const submitLabel   = document.getElementById('task-form-submit-label');
    const errorsBox     = document.getElementById('task-form-errors');
    const errorsList    = errorsBox.querySelector('ul');

    const statusWrap    = document.getElementById('task-status-select');
    const statusTrigger = document.getElementById('task-status-trigger');
    const statusList    = document.getElementById('task-status-list');
    const statusDot     = document.getElementById('task-status-dot');
    const statusValueEl = document.getElementById('task-status-value');
    const statusOptions = Array.from(statusList.querySelectorAll('.kb-select-option'));

    function setStatus(value) {
        const opt = statusOptions.find(o => o.dataset.value === value) || statusOptions[0];
        statusSelect.value   = opt.dataset.value;
        statusValueEl.textContent = opt.querySelector('span:nth-child(2)').textContent;
        statusDot.style.background = opt.querySelector('.kb-select-dot').style.background;
        statusOptions.forEach(o => o.setAttribute('aria-selected', o === opt ? 'true' : 'false'));
    }

    function openStatusDropdown() {
        statusWrap.dataset.open = 'true';
        statusTrigger.setAttribute('aria-expanded', 'true');
    }
    function closeStatusDropdown() {
        statusWrap.dataset.open = 'false';
        statusTrigger.setAttribute('aria-expanded', 'false');
    }
    function toggleStatusDropdown() {
        statusWrap.dataset.open === 'true' ? closeStatusDropdown() : openStatusDropdown();
    }

    statusTrigger.addEventListener('click', (e) => { e.stopPropagation(); toggleStatusDropdown(); });

    statusOptions.forEach(opt => {
        opt.addEventListener('click', () => {
            setStatus(opt.dataset.value);
            closeStatusDropdown();
        });
    });

    document.addEventListener('click', (e) => {
        if (statusWrap.dataset.open === 'true' && !statusWrap.contains(e.target)) {
            closeStatusDropdown();
        }
    });

    function fmtDate(raw) {
        if (!raw) return '';
        const parts = String(raw).slice(0, 10).split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : raw;
    }

    function buildCard(tarefa) {
        const card = document.createElement('div');
        card.className = 'kb-card';
        card.dataset.id          = tarefa.id;
        card.dataset.title       = tarefa.title || '';
        card.dataset.description = tarefa.description || '';

        const top = document.createElement('div');
        top.className = 'kb-card-top';

        const titleSpan = document.createElement('span');
        titleSpan.className = 'kb-card-title';
        titleSpan.textContent = tarefa.title || '';

        const actions = document.createElement('div');
        actions.className = 'kb-actions';

        const editBtn = document.createElement('button');
        editBtn.type = 'button';
        editBtn.className = 'kb-action edit';
        editBtn.title = 'Editar';
        editBtn.innerHTML = SVG_EDIT;

        const delLink = document.createElement('a');
        delLink.className = 'kb-action del';
        delLink.href = `${DELETE_BASE}/${tarefa.id}`;
        delLink.title = 'Excluir';
        delLink.setAttribute('onclick', "return confirm('Deseja realmente excluir esta tarefa?');");
        delLink.innerHTML = SVG_DEL;

        actions.append(editBtn, delLink);
        top.append(titleSpan, actions);
        card.append(top);

        if (tarefa.description) {
            const desc = document.createElement('p');
            desc.className = 'kb-card-desc';
            desc.textContent = tarefa.description;
            card.append(desc);
        }

        const foot = document.createElement('div');
        foot.className = 'kb-card-foot';
        const date = document.createElement('span');
        date.className = 'kb-date';
        date.innerHTML = SVG_CAL;
        date.append(document.createTextNode(' ' + fmtDate(tarefa.created_at)));
        foot.append(date);
        card.append(foot);

        return card;
    }

    function listFor(status) {
        return document.querySelector(`.kb-list[data-status="${status}"]`);
    }

    function placeCard(tarefa, removeId) {
        if (removeId) {
            const old = document.querySelector(`.kb-card[data-id="${removeId}"]`);
            if (old) old.remove();
        }
        const list = listFor(tarefa.status);
        if (!list) return;
        const card = buildCard(tarefa);
        list.insertBefore(card, list.querySelector('.kb-empty') || null);
    }

    function clearErrors() {
        errorsBox.classList.add('hidden');
        errorsList.innerHTML = '';
    }
    function showErrors(errors) {
        errorsList.innerHTML = '';
        Object.values(errors).forEach(msg => {
            const li = document.createElement('li');
            li.textContent = msg;
            errorsList.append(li);
        });
        errorsBox.classList.remove('hidden');
    }

    function openModal(mode, card) {
        clearErrors();
        form.reset();
        closeStatusDropdown();
        form.dataset.mode = mode;

        if (mode === 'edit' && card) {
            form.dataset.id    = card.dataset.id;
            titleInput.value   = card.dataset.title || '';
            descInput.value    = card.dataset.description || '';
            setStatus(card.closest('.kb-list').dataset.status);
            modalTitle.innerHTML   = 'Editar <span class="text-brand-600">Tarefa</span>';
            modalSubtitle.textContent = `Atualizando a tarefa #${card.dataset.id}.`;
            submitLabel.textContent   = 'Atualizar Tarefa';
        } else {
            form.dataset.id    = '';
            setStatus('pendente');
            modalTitle.innerHTML   = 'Nova <span class="text-brand-600">Tarefa</span>';
            modalSubtitle.textContent = 'Crie uma nova atividade para o projeto.';
            submitLabel.textContent   = 'Salvar Tarefa';
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        setTimeout(() => titleInput.focus(), 60);
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        closeStatusDropdown();
        clearErrors();
        form.reset();
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();

        const isEdit = form.dataset.mode === 'edit';
        const url    = isEdit ? `${UPDATE_BASE}/${form.dataset.id}` : STORE_URL;

        const body = new URLSearchParams();
        body.append('title', titleInput.value);
        body.append('description', descInput.value);
        body.append('status', statusSelect.value);
        body.append(CSRF_NAME, CSRF_HASH);

        submitBtn.disabled = true;
        try {
            const res  = await fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body
            });
            const data = await res.json();

            if (data && data.csrf) CSRF_HASH = data.csrf;

            if (res.status === 400 && data.errors) {
                showErrors(data.errors);
                return;
            }
            if (!res.ok || data.status !== 'success') {
                throw new Error((data && data.message) || 'Falha ao salvar a tarefa.');
            }

            placeCard(data.tarefa, isEdit ? data.tarefa.id : null);
            refreshColumns();
            closeModal();
            showToast(data.message || 'Tarefa salva com sucesso!');
        } catch (err) {
            showToast(err.message || 'Erro ao salvar a tarefa.', true);
        } finally {
            submitBtn.disabled = false;
        }
    });

    document.getElementById('btn-nova-tarefa').addEventListener('click', () => openModal('create'));

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.kb-action.edit');
        if (!editBtn) return;
        const card = editBtn.closest('.kb-card');
        if (card) openModal('edit', card);
    });

    modal.querySelectorAll('[data-modal-close]').forEach(el =>
        el.addEventListener('click', closeModal));

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (statusWrap.dataset.open === 'true') { closeStatusDropdown(); return; }
        if (modal.classList.contains('open')) closeModal();
    });
</script>

<?= $this->endSection() ?>
