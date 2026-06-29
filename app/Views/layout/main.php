<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Gerenciador de Tarefas') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="min-h-screen antialiased font-sans">

    <div class="mesh-bg"></div>
    <div class="noise-bg"></div>

    <nav class="sticky top-0 z-50 glass border-b border-brand-600/10">
        <div class="max-w-[1400px] mx-auto px-4 md:px-8 h-16 flex items-center justify-between">
            <a href="<?= site_url('tarefas') ?>" class="flex items-center gap-3 md:gap-4 group">
                <img src="<?= base_url('assets/images/madalozzo_logo.5a60b2e7.svg') ?>" alt="Madalozzo"
                     class="h-7 md:h-8 w-auto group-hover:scale-105 transition-transform">
                <span class="hidden sm:block h-7 w-px bg-brand-600/15"></span>
                <span class="hidden sm:block text-sm md:text-base font-black tracking-tight text-ink uppercase">MadaTask</span>
            </a>
        </div>
    </nav>

    <main class="max-w-[1400px] mx-auto px-4 md:px-8 py-8 md:py-12">
        <?php if (session()->getFlashdata('message')): ?>
            <div class="mb-8 flex items-center gap-3 rounded-2xl border border-brand-200 bg-brand-50/80 px-5 py-4 text-sm font-semibold text-brand-800 shadow-sm">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <?= esc(session()->getFlashdata('message')) ?>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <footer class="max-w-[1400px] mx-auto px-4 md:px-8 py-10 text-center">
        <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-brand-700/50">
        </p>
    </footer>

</body>
</html>
