<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?php $this->insertSlot( 'subject' ) ?></title>
  <style type="text/css">
<?php readfile( WI_ROOT_DIR . '/common/theme/mail.css' ) ?>
  </style>
</head>
<body>

<?php $this->insertContent() ?>

</body>
</html>
