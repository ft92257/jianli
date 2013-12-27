<?php
$config["rewrite"]=array (
  'abs_path' => '/',
  'arg_separator' => '/',
  'gateway' => '',
  'mode' => '',
  'prepend_var_list' => 
  array (
    0 => 'mod',
    1 => 'code',
  ),
  'value_replace_list' => 
  array (
    'mod' => 
    array (
      'topic' => 'blog',
      'tag' => 'tags',
      'profile' => 'personals',
      'member' => 'members',
      'plugin' => 'extends',
    ),
  ),
  'var_replace_list' => 
  array (
    'mod' => 
    array (
    ),
  ),
  'var_separator' => '-',
);
?>