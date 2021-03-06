<?php

/**
 * @Project NUKEVIET 4.x
 * @Author webvang (hoang.nguyen@webvang.vn)
 * @Copyright (C) 2016 webvang. All rights reserved
 * @License: Not free read more http://nukeviet.vn/vi/store/modules/nvtools/
 * @Createdate Wed, 06 Apr 2016 09:05:18 GMT
 */


if (! defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

$page_title = $lang_module['sciencecat'];

if (defined('NV_EDITOR')) {
    require_once NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php';
}


$currentpath = NV_UPLOADS_DIR . '/' . $module_upload;
$error = $admins = '';
$savecat = 0;
list($catid, $parentid, $title, $titlesite, $alias, $description, $descriptionhtml, $keywords, $groups_view, $image, $viewdescription, $subjectcat, $featured) = array(
    0,
    0,
    '',
    '',
    '',
    '',
    '',
    '',
    '6',
    '',
    0,
	'',
    0
);

$groups_list = nv_groups_list();

$parentid = $nv_Request->get_int('parentid', 'get,post', 0);

$catid = $nv_Request->get_int('catid', 'get', 0);

if ($catid > 0 and isset($global_array_sciencecat[$catid])) {
    $parentid = $global_array_sciencecat[$catid]['parentid'];
    $title = $global_array_sciencecat[$catid]['title'];
    $titlesite = $global_array_sciencecat[$catid]['titlesite'];
    $alias = $global_array_sciencecat[$catid]['alias'];
    $description = $global_array_sciencecat[$catid]['description'];
    $descriptionhtml = $global_array_sciencecat[$catid]['descriptionhtml'];
    $viewdescription = $global_array_sciencecat[$catid]['viewdescription'];
    $image = $global_array_sciencecat[$catid]['image'];
    $keywords = $global_array_sciencecat[$catid]['keywords'];
    $groups_view = $global_array_sciencecat[$catid]['groups_view'];
    $featured = $global_array_sciencecat[$catid]['featured'];

    if (! defined('NV_IS_ADMIN_MODULE')) {
        if (!(isset($array_sciencecat_admin[$admin_id][$parentid]) and $array_sciencecat_admin[$admin_id][$parentid]['admin'] == 1)) {
            Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op . '&parentid=' . $parentid);
            die();
        }
    }

    $caption = $lang_module['edit_sciencecat'];
    $array_in_sciencecat = GetCatidInParent($catid);
	$array_in_subjectcat = explode(',', $global_array_sciencecat[$catid]['subjectcat']);
} else {
    $caption = $lang_module['add_sciencecat'];
    $array_in_sciencecat = array();
    $array_in_subjectcat = array();
}

$savecat = $nv_Request->get_int('savecat', 'post', 0);

if (! empty($savecat)) {
    $catid = $nv_Request->get_int('catid', 'post', 0);
    $parentid_old = $nv_Request->get_int('parentid_old', 'post', 0);
    $parentid = $nv_Request->get_int('parentid', 'post', 0);
    $title = $nv_Request->get_title('title', 'post', '', 1);
    $titlesite = $nv_Request->get_title('titlesite', 'post', '', 1);
    $keywords = $nv_Request->get_title('keywords', 'post', '', 1);
    $description = $nv_Request->get_string('description', 'post', '');
    $description = nv_nl2br(nv_htmlspecialchars(strip_tags($description)), '<br />');
    $descriptionhtml = $nv_Request->get_editor('descriptionhtml', '', NV_ALLOWED_HTML_TAGS);

    $viewdescription = $nv_Request->get_int('viewdescription', 'post', 0);
	$array_subjectcat = $nv_Request->get_array('subjectcat', 'post', array( ));
    $featured = $nv_Request->get_int('featured', 'post', 0);

	//môn học
	$i=0;
	
	foreach ($array_subjectcat as $subjectcat_id) {
		if($i==0)
			$subjectcat = $subjectcat.$subjectcat_id;
		else
			$subjectcat = $subjectcat.','.$subjectcat_id;
		$i++;
	}
    // Xử lý liên kết tĩnh
    $_alias = $nv_Request->get_title('alias', 'post', '');
    $_alias = ($_alias == '') ? change_alias($title) : change_alias($_alias);

    if (empty($_alias) or !preg_match("/^([a-zA-Z0-9\_\-]+)$/", $_alias)) {
        if (empty($alias)) {
            if ($catid) {
                $alias = 'cat-' . $catid;
            } else {
                $_m_sciencecatid = $db->query('SELECT MAX(catid) AS cid FROM ' . NV_PREFIXLANG . '_' . $module_data . '_sciencecat')->fetchColumn();

                if (empty($_m_sciencecatid)) {
                    $alias = 'cat-1';
                } else {
                    $alias = 'cat-' . (intval($_m_sciencecatid) + 1);
                }
            }
        }
    } else {
        $alias = $_alias;
    }

    $_groups_post = $nv_Request->get_array('groups_view', 'post', array());
    $groups_view = !empty($_groups_post) ? implode(',', nv_groups_post(array_intersect($_groups_post, array_keys($groups_list)))) : '';

    $image = $nv_Request->get_string('image', 'post', '');
    if (nv_is_file($image, NV_UPLOADS_DIR . '/' . $module_upload)) {
        $lu = strlen(NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_upload . '/');
        $image = substr($image, $lu);
    } else {
        $image = '';
    }

    if (! defined('NV_IS_ADMIN_MODULE')) {
        if (!(isset($array_sciencecat_admin[$admin_id][$parentid]) and $array_sciencecat_admin[$admin_id][$parentid]['admin'] == 1)) {
            Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op . '&parentid=' . $parentid);
            die();
        }
    }

    if ($catid == 0 and $title != '') {
        $weight = $db->query('SELECT max(weight) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_sciencecat WHERE parentid=' . $parentid)->fetchColumn();
        $weight = intval($weight) + 1;
        $viewcat = 'viewcat_page_new';
        $subcatid = '';

        $sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $module_data . "_sciencecat (parentid, title, titlesite, alias, description, descriptionhtml, image, viewdescription, weight, sort, lev, viewcat, numsubcat, subcatid, inhome, numlinks, newday,featured, keywords, admins, add_time, edit_time, groups_view, subjectcat) VALUES
			(:parentid, :title, :titlesite, :alias, :description, :descriptionhtml, '', '" . $viewdescription . "', :weight, '0', '0', :viewcat, '0', :subcatid, '1', '3', '2',:featured, :keywords, :admins, " . NV_CURRENTTIME . ", " . NV_CURRENTTIME . ", :groups_view, :subjectcat)";

        $data_insert = array();
        $data_insert['parentid'] = $parentid;
        $data_insert['title'] = $title;
        $data_insert['titlesite'] = $titlesite;
        $data_insert['alias'] = $alias;
        $data_insert['description'] = $description;
        $data_insert['descriptionhtml'] = $descriptionhtml;
        $data_insert['weight'] = $weight;
        $data_insert['viewcat'] = $viewcat;
        $data_insert['subcatid'] = $subcatid;
        $data_insert['keywords'] = $keywords;
        $data_insert['admins'] = $admins;
        $data_insert['groups_view'] = $groups_view;
        $data_insert['subjectcat'] = $subjectcat;
        $data_insert['featured'] = $featured;

        $newcatid = $db->insert_id($sql, 'catid', $data_insert);
        if ($newcatid > 0) {

            
            nv_fix_sciencecat_order();
			
            if (! defined('NV_IS_ADMIN_MODULE')) {
                $db->query('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_admins (userid, catid, admin, add_content, pub_content, edit_content, del_content) VALUES (' . $admin_id . ', ' . $newcatid . ', 1, 1, 1, 1, 1)');
            }

            $nv_Cache->delMod($module_name);
            nv_insert_logs(NV_LANG_DATA, $module_name, $lang_module['add_sciencecat'], $title, $admin_info['userid']);
            Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op . '&parentid=' . $parentid);
            die();
        } else {
            $error = $lang_module['errorsave'];
        }
    } elseif ($catid > 0 and $title != '') {
        $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_sciencecat SET parentid= :parentid, title= :title, titlesite=:titlesite, alias = :alias, description = :description, descriptionhtml = :descriptionhtml, image= :image, viewdescription= :viewdescription,featured=:featured, keywords= :keywords, groups_view= :groups_view, subjectcat= :subjectcat, edit_time=' . NV_CURRENTTIME . ' WHERE catid =' . $catid);
        $stmt->bindParam(':parentid', $parentid, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':titlesite', $titlesite, PDO::PARAM_STR);
        $stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':viewdescription', $viewdescription, PDO::PARAM_STR);
        $stmt->bindParam(':keywords', $keywords, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR, strlen($description));
        $stmt->bindParam(':descriptionhtml', $descriptionhtml, PDO::PARAM_STR, strlen($descriptionhtml));
        $stmt->bindParam(':groups_view', $groups_view, PDO::PARAM_STR);
        $stmt->bindParam(':subjectcat', $subjectcat, PDO::PARAM_STR);
        $stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount()) {
            if ($parentid != $parentid_old) {
                $weight = $db->query('SELECT max(weight) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_sciencecat WHERE parentid=' . $parentid)->fetchColumn();
                $weight = intval($weight) + 1;

                $sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_sciencecat SET weight=' . $weight . ' WHERE catid=' . intval($catid);
                $db->query($sql);

                nv_fix_sciencecat_order();
                nv_insert_logs(NV_LANG_DATA, $module_name, $lang_module['edit_sciencecat'], $title, $admin_info['userid']);
            }

            $nv_Cache->delMod($module_name);
            Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op . '&parentid=' . $parentid);
            die();
        } else {
            $error = $lang_module['errorsave'];
        }
    } else {
        $error = $lang_module['error_name'];
    }
}

$groups_view = explode(',', $groups_view);

$array_sciencecat_list = array();
if (defined('NV_IS_ADMIN_MODULE')) {
    $array_sciencecat_list[0] = $lang_module['cat_sub_sl'];
}
foreach ($global_array_sciencecat as $catid_i => $array_value) {
    $lev_i = $array_value['lev'];
    if (defined('NV_IS_ADMIN_MODULE') or (isset($array_sciencecat_admin[$admin_id][$catid_i]) and $array_sciencecat_admin[$admin_id][$catid_i]['admin'] == 1)) {
        $xtitle_i = '';
        if ($lev_i > 0) {
            $xtitle_i .= '&nbsp;&nbsp;&nbsp;|';
            for ($i = 1; $i <= $lev_i; ++$i) {
                $xtitle_i .= '---';
            }
            $xtitle_i .= '>&nbsp;';
        }
        $xtitle_i .= $array_value['title'];
        $array_sciencecat_list[$catid_i] = $xtitle_i;
    }
}
foreach ($global_array_subjectcat as $catid_i => $array_value) {
    $lev_i = $array_value['lev'];
    if (defined('NV_IS_ADMIN_MODULE') or (isset($array_subjectcat_admin[$admin_id][$catid_i]) and $array_subjectcat_admin[$admin_id][$catid_i]['admin'] == 1)) {
        $xtitle_i = '';
        if ($lev_i > 0) {
            $xtitle_i .= '&nbsp;&nbsp;&nbsp;|';
            for ($i = 1; $i <= $lev_i; ++$i) {
                $xtitle_i .= '---';
            }
            $xtitle_i .= '>&nbsp;';
        }
        $xtitle_i .= $array_value['title'];
        $array_subjectcat_list[$catid_i] = $xtitle_i;
    }
}


if (!empty($array_sciencecat_list)) {
    $cat_listsub = array();
    while (list($catid_i, $title_i) = each($array_sciencecat_list)) {
        if (!in_array($catid_i, $array_in_sciencecat)) {
            $cat_listsub[] = array(
                'value' => $catid_i,
                'selected' => ($catid_i == $parentid) ? ' selected="selected"' : '',
                'title' => $title_i
            );
        }
    }
	if (!empty($array_subjectcat_list)) {
		$subjectcat_listsub = array();
		while (list($catid_i, $title_i) = each($array_subjectcat_list)) {
			
				$sl = (in_array($catid_i, $array_in_subjectcat)) ? ' selected="selected"' : '';
				$subjectcat_listsub[] = array(
					'value' => $catid_i,
					'selected' => $sl,
					'title' => $title_i
				);
			
		}
	}

    $groups_views = array();
    foreach ($groups_list as $group_id => $grtl) {
        $groups_views[] = array(
            'value' => $group_id,
            'checked' => in_array($group_id, $groups_view) ? ' checked="checked"' : '',
            'title' => $grtl
        );
    }
}

$lang_global['title_suggest_max'] = sprintf($lang_global['length_suggest_max'], 65);
$lang_global['description_suggest_max'] = sprintf($lang_global['length_suggest_max'], 160);

if (!empty($image) and file_exists(NV_UPLOADS_REAL_DIR . '/' . $module_upload . '/' . $image)) {
    $image = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_upload . '/' . $image;
    $currentpath = dirname($image);
}

$xtpl = new XTemplate('sciencecat.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);

$xtpl->assign('caption', $caption);
$xtpl->assign('catid', $catid);
$xtpl->assign('title', $title);
$xtpl->assign('titlesite', $titlesite);
$xtpl->assign('alias', $alias);
$xtpl->assign('parentid', $parentid);
$xtpl->assign('keywords', $keywords);
$xtpl->assign('description', nv_htmlspecialchars(nv_br2nl($description)));
$xtpl->assign('CAT_LIST', nv_show_sciencecat_list($parentid));
$xtpl->assign('UPLOAD_CURRENT', $currentpath);
$xtpl->assign('UPLOAD_PATH', NV_UPLOADS_DIR . '/' . $module_upload);
$xtpl->assign('image', $image);

for ($i = 0; $i <= 2; $i++) {
    $data = array(
        'value' => $i,
        'selected' => ($viewdescription == $i) ? ' checked="checked"' : '',
        'title' => $lang_module['viewdescription_' . $i]
    );
    $xtpl->assign('VIEWDESCRIPTION', $data);
    $xtpl->parse('main.content.viewdescription');
}
if ($catid > 0) {
    $sql = 'SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_science WHERE status=1 ORDER BY publtime DESC LIMIT 100';
    $result = $db->query($sql);
    $array_id=array();
    $array_id[] = $featured;
    while ($row = $result->fetch()) {
        $array_id[] = $row['id'] ;
    }

    $sql1 = 'SELECT id, title FROM ' . NV_PREFIXLANG . '_' . $module_data . '_science  WHERE id IN ('.implode(',', $array_id).') ORDER BY publtime DESC';
    $result = $db->query($sql1);

    while ($row = $result->fetch()) {
        $row = array(
            'id' => $row['id'],
            'selected' => ($featured == $row['id']) ? ' selected="selected"' : '',
            'title' => $row['title']
        );
        $xtpl->assign('FEATURED_NEWS', $row);
        $xtpl->parse('main.content.featured.featured_loop');
    }
    $xtpl->parse('main.content.featured');
}

if (!empty($error)) {
    $xtpl->assign('ERROR', $error);
    $xtpl->parse('main.error');
}

if (!empty($array_sciencecat_list)) {
    if (empty($alias)) {
        $xtpl->parse('main.content.getalias');
    }

    foreach ($cat_listsub as $data) {
        $xtpl->assign('cat_listsub', $data);
        $xtpl->parse('main.content.cat_listsub');
    }
    foreach ($subjectcat_listsub as $data) {
        $xtpl->assign('subjectcat_listsub', $data);
        $xtpl->parse('main.content.subject_loop');
    }
    foreach ($groups_views as $data) {
        $xtpl->assign('groups_views', $data);
        $xtpl->parse('main.content.groups_views');
    }

    $descriptionhtml = nv_htmlspecialchars(nv_editor_br2nl($descriptionhtml));
    if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor')) {
        $_uploads_dir = NV_UPLOADS_DIR . '/' . $module_upload;
        $descriptionhtml = nv_aleditor('descriptionhtml', '100%', '200px', $descriptionhtml, 'Basic', $_uploads_dir, $_uploads_dir);
    } else {
        $descriptionhtml = "<textarea style=\"width: 100%\" name=\"descriptionhtml\" id=\"descriptionhtml\" cols=\"20\" rows=\"15\">" . $descriptionhtml . "</textarea>";
    }
    $xtpl->assign('DESCRIPTIONHTML', $descriptionhtml);

    $xtpl->parse('main.content');
}


$xtpl->parse('main');
$contents .= $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';