<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename plugin.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-24 16:33:35 1404877762 2106603301 3464 $
 *******************************************************************/



class plugin
{
	var $identifier = '';	function plugin()
	{
			
	}

	
	function get_plugin_info()
	{
		global $plugin_info, $DB;
		$query = DB::query("SELECT *  FROM ".DB::table('plugin')." WHERE identifier = '".$this->identifier."'");
		return  DB::fetch($query);

	}

	function add_hook($identifier)
	{
		global $fun, $TemplateHandler;
		$class_str = 'plugin_'.$identifier;
		$hookobj = new $class_str;
		$TemplateHandler->hookfuns($hookobj);
			}
	
	function add_filter($hook_tag, $function_to_add, $priority = 10, $accepted_args)
	{
		global $hook_array;
		$ids = $this->filter_build_unique_id($hook_tag, $function_to_add, $priority);
		$hook_array[$hook_tag][$priority] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
		return true;
	}

	
	function add_action($hook_tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		$this->add_filter($hook_tag, $function_to_add, $priority = 10, $accepted_args);
	}

	function do_action($tag, $arg = '')
	{
		global $hook_array;
		$args = array();
		if(is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) )
		{
			$args[] =& $arg[0];
		}
		else
		{
			$args = $arg;
		}

		if($hook_array[$tag])
		{
			foreach((array)current($hook_array[$tag]) as $temp)
			{
				if(!is_null($temp['function']))
				{
					call_user_func_array($temp, $args);
				}
			}
		}
	}

		function get_files_plugins()
	{
		$plugins = array();
		$files_plugins = array('test/index.php', 'hello.php');		foreach($files_plugins as $plugin )
		{
			if (!$this->validate_file( $plugin )			&& '.php' == substr( $plugin, -4) 			&& file_exists( PLUGIN_DIR . '/'.$plugin ) 			)
			$plugins[] = PLUGIN_DIR . '/' . $plugin;
		}
		return $plugins;
	}

	function validate_file($file, $allowed_files = '' )
	{
		if ( false !== strpos( $file, '..' ))
		{
			return 1;
		}
		elseif( false !== strpos( $file, './' ))
		{
			return 1;
		}
		elseif (!empty ( $allowed_files ) && (!in_array( $file, $allowed_files )))
		{
			return 3;
		}
		elseif (':' == substr( $file, 1, 1 ))
		{
			return 2;
		}
		else
		{
			return 0;
		}
	}

	function filter_build_unique_id($tag, $function, $priority)
	{
		static $filter_id_count = 0;
		if (is_string($function))
		{
			return $function;
		}
		if(is_object($function) )
		{
						$function = array($function, '');
		}
		else
		{
			$function = array($function);
		}
		if(is_object($function))
		{
			if(function_exists('spl_object_hash'))
			{
				return spl_object_hash($function[0]).$function[1];
			}
			else
			{
				$obj_idx = get_class($function[0]).$function[1];
				if ( !isset($function[0]->wp_filter_id) ){
					if ( false === $priority )
					return false;
					$obj_idx .= isset($wp_filter[$tag][$priority]) ? count((array)$wp_filter[$tag][$priority]) : $filter_id_count;
					$function[0]->wp_filter_id = $filter_id_count;
					++$filter_id_count;
				}
				else
				{
					$obj_idx .= $function[0]->wp_filter_id;
				}
			}
			return $obj_idx;
		}
		else
		{
			return $function[0].$function[1];
		}
	}
}
?>