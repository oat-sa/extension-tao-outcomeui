<script>
    require([
            'jquery',
            'jquery.fileDownload'
        ],
        function($) {
            $.fileDownload('<?=get_data("url")?>', {
                httpMethod: 'GET',
                data: {uri: '<?=get_data("uri")?>'}
            });
        });
</script>