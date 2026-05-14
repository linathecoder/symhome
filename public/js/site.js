window.addEventListener('DOMContentLoaded', function() {
    var cards = document.querySelectorAll('.product-card');
    cards.forEach(function(card) {
        card.addEventListener('mousemove', function(event) {
            var bounds = card.getBoundingClientRect();
            var x = event.clientX - bounds.left - bounds.width / 2;
            var y = event.clientY - bounds.top - bounds.height / 2;
            card.style.transform = 'perspective(900px) rotateX(' + (-y / 28) + 'deg) rotateY(' + (x / 24) + 'deg)';
        });
        card.addEventListener('mouseleave', function() {
            card.style.transform = '';
        });
    });
    var buttons = document.querySelectorAll('.btn');
    buttons.forEach(function(button) {
        button.addEventListener('pointerenter', function() {
            button.style.filter = 'brightness(1.05)';
        });
        button.addEventListener('pointerleave', function() {
            button.style.filter = '';
        });
    });
    var chart = document.getElementById('adminChart');
    if (chart && window.Chart) {
        var stats = [];
        try {
            stats = JSON.parse(chart.dataset.stats || '[]');
        } catch (e) {
            stats = [];
        }
        new Chart(chart, {
            type: 'bar',
            data: {
                labels: ['Chiffre', 'Commandes', 'Produits', 'Utilisateurs'],
                datasets: [{
                    data: stats,
                    backgroundColor: ['#1a202c', '#0d6efd', '#20c997', '#6f42c1'],
                    borderRadius: 16,
                    barThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#4b5563' } },
                    y: { grid: { color: 'rgba(15,23,42,0.08)' }, ticks: { color: '#4b5563' } }
                }
            }
        });
    }
    var revealItems = document.querySelectorAll('.product-card, .dashboard-card, .card');
    revealItems.forEach(function(item) {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
    });
    window.requestAnimationFrame(function() {
        revealItems.forEach(function(item, index) {
            setTimeout(function() {
                item.style.transition = 'opacity 0.55s ease, transform 0.55s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 75);
        });
    });
});
