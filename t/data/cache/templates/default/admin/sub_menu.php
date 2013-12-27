<?php /* 2012-08-31 in jishigou invalid request template */ if(!defined("IN_JISHIGOU")) exit("invalid request"); ?>
<?php $sub_menu_list = $_sub_menu_list?$_sub_menu_list:get_sub_menu(); ?> <?php if($sub_menu_list) { ?> <div class="nav3"> <ul class="cc"> <?php if(is_array($sub_menu_list)) { foreach($sub_menu_list as $value) { ?> <?php if($value['type'] == '1' && PLUGINDEVELOPER < 1)continue; ?> <li 
<?php if($value['current']) { ?>
class="current"
<?php } ?>
> <?php if($this->pluginid) { ?> <a href="<?php echo $value['link']; ?>&id=<?php echo $this->pluginid; ?>"> <?php } else { ?><a href="<?php echo $value['link']; ?>"> <?php } ?> <?php echo $value['name']; ?></a> </li> <?php } } ?> </ul> </div> <?php } ?>