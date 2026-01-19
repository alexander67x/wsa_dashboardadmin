<script>
    window.addEventListener('load', function () {
        if (window.FilePond) {
            window.FilePond.setOptions({
                // Usamos la misma estructura HTML que FilePond espera
                // para que la parte de "buscar" siga funcionando bien.
                labelIdle: 'Arrastra y suelta tus archivos o <span class="filepond--label-action">haz clic para buscar</span>',
            });
        }
    });
</script>
