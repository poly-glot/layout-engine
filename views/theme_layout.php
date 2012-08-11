<?php

//Layout sub-tabs support
$layout_list_types = array('list'=>__('List','layout-engine'),'tree'=>__('Tree','layout-engine'));
$layout_list_types = apply_filters('le_layout_list_types', $layout_list_types);
$layout_type = strtolower($_REQUEST['layout_type']);
if(empty($layout_type))
{
	if(isset($_GET['prev_layout_type']))
	{
		$layout_type = $_GET['prev_layout_type'];
	}else{
		$layout_type = 'list';
	}
}

$link_default = add_query_arg("prev_layout_type", $layout_type);
$link_default = remove_query_arg("layout_type", $link_default);


if (!empty( $layout_list_types ) )
{

	echo "<ul class='subsubsub'>\n";
		
	$list = array();
	foreach($layout_list_types as $k=>$v)
	{
		$link = add_query_arg("layout_type", $k);
		$link = remove_query_arg("prev_layout_type", $link);

		if($k == $layout_type)
		{
			$list[] = sprintf('<li><a href="%s" class="current">%s</a> ', $link, $v);
		}else{
			$list[] = sprintf('<li><a href="%s">%s</a> ', $link, $v);
		}
	}
		
	echo implode( " |</li>\n", $list ) . "</li>\n";
	echo "</ul>";
}

//Getting the tree
$tree = LE_Base::getTree();

?>
<div id="template_hierarchy_<?php echo $layout_type; ?>" class="clearfix layout_engine_suboptions">
	<ul>
		<?php LE_Admin::layout_tree_walk($tree, null, null, $link_default); ?>
	</ul>
</div>	<!-- template_hierarchy -->