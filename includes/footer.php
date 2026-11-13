</main>
<footer class="footer">Task Tracker · PHP + MySQL</footer>
<script>
document.querySelectorAll('[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!window.confirm(form.getAttribute('data-confirm'))) event.preventDefault();
    });
});
</script>
</body>
</html>
