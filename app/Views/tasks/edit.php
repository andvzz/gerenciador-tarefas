<?php
$coresStatus = [
    'pendente'     => '#7EAEE7',
    'em andamento' => '#0000FF',
    'concluída'    => '#22C55E',
];
?>

<div id="edit-modal" class="kb-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
    <div class="kb-modal-overlay" data-modal-close></div>

    <div class="kb-modal-panel">
        <button type="button" class="kb-modal-close" data-modal-close title="Fechar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div class="kb-modal-body">
            <div class="space-y-1 mb-7 pr-10">
                <h2 id="edit-modal-title" class="text-2xl md:text-3xl font-black tracking-tighter uppercase text-ink">
                    Editar <span class="text-brand-600">Tarefa</span>
                </h2>
                <p id="edit-modal-subtitle" class="text-sm font-medium text-brand-900/50">Atualize os dados da tarefa selecionada.</p>
            </div>

            <div id="edit-form-errors" class="mb-6 rounded-2xl border border-red-200 bg-red-50/80 px-5 py-4 hidden">
                <p class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-red-600 mb-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Corrija os erros abaixo
                </p>
                <ul class="list-disc list-inside space-y-1 text-sm font-medium text-red-700"></ul>
            </div>

            <form id="edit-form" class="flex flex-col gap-6" action="<?= site_url('tasks/update') ?>" method="post" data-mode="edit">
                <?= csrf_field() ?>

                <div class="flex flex-col gap-2">
                    <label for="edit-title" class="text-[11px] font-black uppercase tracking-[0.15em] text-brand-900/60 px-1">Título</label>
                    <input type="text" id="edit-title" name="title" required
                           placeholder="Ex: Revisar proposta do cliente"
                           class="w-full rounded-xl border border-brand-600/15 bg-white/70 px-4 py-3 text-sm font-medium text-ink placeholder:text-brand-900/30 outline-none transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30">
                </div>

                <div class="flex flex-col gap-2">
                    <label for="edit-description" class="text-[11px] font-black uppercase tracking-[0.15em] text-brand-900/60 px-1">Descrição <span class="text-brand-900/30 normal-case tracking-normal font-semibold">(opcional)</span></label>
                    <textarea id="edit-description" name="description" rows="4"
                              placeholder="Adicione detalhes, links ou instruções..."
                              class="w-full rounded-xl border border-brand-600/15 bg-white/70 px-4 py-3 text-sm font-medium text-ink placeholder:text-brand-900/30 outline-none transition-all resize-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"></textarea>
                </div>

                <div class="flex flex-col gap-2">
                    <span id="edit-status-label" class="text-[11px] font-black uppercase tracking-[0.15em] text-brand-900/60 px-1">Status</span>

                    <input type="hidden" id="edit-status" name="status" value="pendente">

                    <div id="edit-status-select" class="kb-select" data-open="false">
                        <button type="button" id="edit-status-trigger" class="kb-select-trigger"
                                aria-haspopup="listbox" aria-expanded="false" aria-labelledby="edit-status-label">
                            <span id="edit-status-dot" class="kb-select-dot" style="background: <?= $coresStatus['pendente'] ?>;"></span>
                            <span id="edit-status-value" class="kb-select-value">Pendente</span>
                            <svg class="kb-select-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>

                        <ul id="edit-status-list" class="kb-select-list" role="listbox" aria-labelledby="edit-status-label">
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
                    <button type="submit" id="edit-form-submit"
                            class="flex-[2] inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-brand-600 text-white text-sm font-black uppercase tracking-widest shadow-lg shadow-brand-600/30 hover:bg-brand-700 hover:scale-[1.01] active:scale-95 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span id="edit-form-submit-label">Atualizar Tarefa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
