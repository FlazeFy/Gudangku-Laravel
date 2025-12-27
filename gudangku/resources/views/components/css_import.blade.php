<link rel="stylesheet" href="{{ asset('/global_v1.0.css') }}"/>
<link rel="stylesheet" href="{{ asset('/button_v1.0.css') }}"/>
<link rel="stylesheet" href="{{ asset('/container_v1.0.css') }}"/>
<link rel="stylesheet" href="{{ asset('/form_v1.0.css') }}"/>
<link rel="stylesheet" href="{{ asset('/chart_v1.0.css') }}"/>
<link rel="stylesheet" href="{{ asset('/typography_v1.0.css') }}"/>

<?php if(preg_match('(chat)', $cleanedUrl)): ?>
    <link rel="stylesheet" href="{{ asset('/chat_v1.0.css') }}"/>
<?php endif; ?>

<?php if(preg_match('(report/add|report/detail)', $cleanedUrl)): ?>
    <link rel="stylesheet" href="{{ asset('/report_item_v1.0.css') }}"/>
<?php endif; ?>