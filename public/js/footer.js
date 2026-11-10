document.addEventListener('DOMContentLoaded', function () {

    const btn = document.getElementById('ftTop');
    if (!btn) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });

});