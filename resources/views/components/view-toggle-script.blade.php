@props(['scope'])

{{-- Pre-apply the stored Cards/Table view before Alpine boots (desktop only). --}}
<script>
    (function () {
        var view = localStorage.getItem('shoeboy.{{ $scope }}.view') || 'cards';
        document.querySelectorAll('[data-view-panel]').forEach(function (el) {
            el.style.display = el.getAttribute('data-view-panel') === view ? '' : 'none';
        });
    })();
</script>
