<?php

if (!isset($_POST["save"])){
	if (isset($id) && !empty($id)) {

		$nav_tpl = functions::mdl_load("templates-e/edit/nav-tab-item.tpl");
		$nav_content_tpl = functions::mdl_load("templates-e/edit/tab-content-item-input.tpl");
		$option_item_tpl = functions::mdl_load("templates-e/edit/option-item.tpl");
		$tabs = "";
		$nav_content = "";

		// Return all article info
		$article = new article();
		$article->setId($id);
		$article_result = $article->returnOneArticleAllLanguages();

		$i = 0;
		foreach ($cfg->lg as $index=>$lg) {
			if ($lg[0]) {
				$tabs .= functions::c2r(
					[
						"class" => ($i == 0) ? "active" : "",
						"nr" => $index,
						"lang-name" => $lg[2]
					],
					$nav_tpl
				);

				$nav_content .= functions::c2r(
					[
						"class" => ($i == 0) ? "active" : "",
						"nr" => $index,
						"label-name" => $mdl_lang["label"]["name"],
						"label-description" => $mdl_lang["label"]["description"],
						"place-holder-name" => "",
						"place-holder-text" => "",
						"name-value" => (isset($article_result[$index]->title)) ? htmlspecialchars($article_result[$index]->title) : "",
						"description-value" => (isset($article_result[$index]->text)) ? $article_result[$index]->text : ""
					],
					$nav_content_tpl
				);
				$i++;
			}
		}

		$category = new category();
		$category->setLangId(1);
		$category = $category->returnAllCategories();

		/*------------------------------------------*/

		function recursiveWayGet($id, $i){
			global $parent_options, $option_item_tpl, $article_result;

			$a = new category();
			$a->setLangId(1);
			$a->setParentId($id);
			$a = $a->returnSubCategoriesFromOneCategory();
			$i++;

			foreach ($a as $item) {
				if ($item->id != $id) {
					$parent_options .= functions::c2r(
						[
							"option-id" => $item->id,
							"option" => sprintf("%s> %s", str_repeat("-", $i), $item->title),
							"selected" => ($item->id == $article_result[1]->category_id) ? "selected" : ""
						],
						$option_item_tpl
					);
				}

				if ($item->nr_sub_cats > 0) {
					recursiveWayGet($item->id, $i);
				}
			}
		}

		$mainCategories = new category();
		$mainCategories->setLangId(1);
		$allCats = $mainCategories->returnAllMainCategories();

		foreach ($allCats as $item) {
			if (!isset($parent_options)) {
				$parent_options = "";
			}

			if ($item->id != $id) {
				$parent_options .= functions::c2r(
					[
						"option-id" => $item->id,
						"option" => $item->title,
						"selected" => ($item->id == $article_result[1]->category_id) ? "selected" : ""
					],
					$option_item_tpl
				);
			}
			recursiveWayGet($item->id, 0);
		}

		$mdl = functions::c2r(
			[
				"content" => functions::mdl_load("templates-e/edit/form.tpl"),

				"tabs-categories-name-description" => functions::mdl_load("templates-e/edit/tabs.tpl"),

				"nav-tabs-items" => $tabs,
				"tab-content-items" => $nav_content,

				"type" => $mdl_lang["label"]["type"],
				"select-option-type" => $mdl_lang["form"]["option-type"],
				"parent" => $mdl_lang["label"]["parent"],
				"select-option-parent" => $mdl_lang["form"]["option-parent"],
				"select-option-parent-no" => $mdl_lang["form"]["option-parent-no"],
				"selected" => ($article_result[1]->category_id == -1) ? "selected" : "",
				"parent-options" => (isset($parent_options)) ? $parent_options : "",
				"date" => $mdl_lang["label"]["date"],
				"date-placeholder" => $mdl_lang["form"]["date-placeholder"],
				"date-value" => $article_result[1]->date,
				"code" => $mdl_lang["label"]["code"],
				"code-placeholder" => $mdl_lang["label"]["code-placeholder"],
				"code-value" => $article_result[1]->code,
				"published" => $mdl_lang["label"]["published"],
				"published-checked" => ($article_result[1]->published) ? "checked" : "",
				"but-submit" => $mdl_lang["label"]["but-submit"]
			],
			functions::mdl_load("templates/add.tpl")
		);
	} else {
		// if doesn't exist an action response, system sent you to 404
		header("Location: {$cfg->system->path_bo}/0/{$lg_s}/404/");
	}
} else {
	$article = new article();

	$article->setId($id);
	$article->setContent($_POST["name"], $_POST["description"]);
	$article->setCategoryId($_POST["category-parent"]);
	$article->setCode($_POST["code"]);
	$article->setDate($_POST["date"]);
	$article->setDateUpdate();
	$article->setPublished(isset($_POST["published"]) ? $_POST["published"] : 0);
	$article->setUserId($authData["id"]);

	if ($article->update()) {
		$textToPrint = $mdl_lang["add"]["success"];
	} else {
		$textToPrint = $mdl_lang["add"]["failure"];
	}

	$mdl = functions::c2r(["content" => (isset($textToPrint)) ? $textToPrint : ""], functions::mdl_load("templates/result.tpl"));
}

functions::importPlg ("files", ["id" => $id, "module" => "article"]);

include "pages/module-core.php";
