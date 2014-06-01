<?php
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}

qa_register_plugin_module('editor', '/addons/editor/editor.php', 'qw_editor', 'QW Editor');