<?php

/**
 * @copyright Metaways Infosystems GmbH, 2011
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015
 */

return array(
	'delete' => array(
		'ansi' => '
			DELETE FROM "mshop_attribute"
			WHERE :cond AND siteid = ?
		'
	),
	'insert' => array(
		'ansi' => '
			INSERT INTO "mshop_attribute" (
				"siteid", "typeid", "domain", "code", "status", "pos", "label",
				"mtime", "editor", "ctime"
			) VALUES (
				?, ?, ?, ?, ?, ?, ?, ?, ?, ?
			)
		'
	),
	'update' => array(
		'ansi' => '
			UPDATE "mshop_attribute"
			SET "siteid" = ?, "typeid" = ?, "domain" = ?, "code" = ?,
				"status" = ?, "pos" = ?, "label" = ?, "mtime" = ?,
				"editor" = ?
			WHERE "id" = ?
		'
	),
	'search' => array(
		'ansi' => '
			SELECT DISTINCT matt."id" AS "attribute.id", matt."siteid" AS "attribute.siteid",
				matt."typeid" AS "attribute.typeid", matt."domain" AS "attribute.domain",
				matt."code" AS "attribute.code", matt."status" AS "attribute.status",
				matt."pos" AS "attribute.position", matt."label" AS "attribute.label",
				matt."mtime" AS "attribute.mtime", matt."ctime" AS "attribute.ctime",
				matt."editor" AS "attribute.editor"
			FROM "mshop_attribute" AS matt
			:joins
			WHERE :cond
			/*-orderby*/ ORDER BY :order /*orderby-*/
			LIMIT :size OFFSET :start
		'
	),
	'count' => array(
		'ansi' => '
			SELECT COUNT(*) AS "count"
			FROM (
				SELECT DISTINCT matt."id"
				FROM "mshop_attribute" AS matt
				:joins
				WHERE :cond
				LIMIT 10000 OFFSET 0
			) AS list
		'
	),
	'newid' => array(
		'mysql' => 'SELECT LAST_INSERT_ID()'
	),
);

