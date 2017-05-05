<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2017
 */


return array(
	'manager' => array(
		'attribute' => array(
			'standard' => array(
				'delete' => array(
					'ansi' => '
						DELETE FROM "mshop_index_attribute"
						WHERE :cond AND "siteid" = ?
					'
				),
				'insert' => array(
					'ansi' => '
						INSERT INTO "mshop_index_attribute" (
							"prodid", "siteid", "attrid", "listtype", "type", "code",
							"mtime", "editor", "ctime"
						) VALUES (
							?, ?, ?, ?, ?, ?, ?, ?, ?
						)
					',
					'pgsql' => '
						INSERT INTO "mshop_index_attribute" (
							"prodid", "siteid", "attrid", "listtype", "type", "code",
							"mtime", "editor", "ctime"
						) VALUES (
							?, ?, ?, ?, ?, ?, ?, ?, ?
						)
						ON CONFLICT DO NOTHING
					',
				),
				'search' => array(
					'ansi' => '
						SELECT mpro."id"
						FROM "mshop_product" AS mpro
						:joins
						WHERE :cond
						GROUP BY mpro."id" /*-columns*/ , :columns /*columns-*/
						/*-orderby*/ ORDER BY :order /*orderby-*/
						LIMIT :size OFFSET :start
					'
				),
				'count' => array(
					'ansi' => '
						SELECT COUNT(*) AS "count"
						FROM (
							SELECT DISTINCT mpro."id"
							FROM "mshop_product" AS mpro
							:joins
							WHERE :cond
							LIMIT 1000 OFFSET 0
						) AS list
					'
				),
				'cleanup' => array(
					'ansi' => '
						DELETE FROM "mshop_index_attribute"
						WHERE "ctime" < ? AND "siteid" = ?
					'
				),
				'optimize' => array(
					'mysql' => array(
						'OPTIMIZE TABLE "mshop_index_attribute"',
					),
					'pgsql' => [],
				),
			),
		),
		'catalog' => array(
			'standard' => array(
				'delete' => array(
					'ansi' => '
						DELETE FROM "mshop_index_catalog"
						WHERE :cond AND "siteid" = ?
					'
				),
				'insert' => array(
					'ansi' => '
						INSERT INTO "mshop_index_catalog" (
							"prodid", "siteid", "catid", "listtype", "pos", "mtime",
							"editor", "ctime"
						) VALUES (
							?, ?, ?, ?, ?, ?, ?, ?
						)
					',
					'pgsql' => '
						INSERT INTO "mshop_index_catalog" (
							"prodid", "siteid", "catid", "listtype", "pos", "mtime",
							"editor", "ctime"
						) VALUES (
							?, ?, ?, ?, ?, ?, ?, ?
						)
						ON CONFLICT DO NOTHING
					'
				),
				'search' => array(
					'ansi' => '
						SELECT mpro."id"
						FROM "mshop_product" AS mpro
						:joins
						WHERE :cond
						GROUP BY mpro."id" /*-columns*/ , :columns /*columns-*/
						/*-orderby*/ ORDER BY :order /*orderby-*/
						LIMIT :size OFFSET :start
					'
				),
				'count' => array(
					'ansi' => '
						SELECT COUNT(*) AS "count"
						FROM (
							SELECT DISTINCT mpro."id"
							FROM "mshop_product" AS mpro
							:joins
							WHERE :cond
							LIMIT 1000 OFFSET 0
						) AS list
					'
				),
				'cleanup' => array(
					'ansi' => '
						DELETE FROM "mshop_index_catalog"
						WHERE "ctime" < ? AND "siteid" = ?
					'
				),
				'optimize' => array(
					'mysql' => array(
						'OPTIMIZE TABLE "mshop_index_catalog"',
					),
					'pgsql' => [],
				),
			),
		),
		'price' => array(
			'standard' => array(
				'delete' => array(
					'ansi' => '
						DELETE FROM "mshop_index_price"
						WHERE :cond AND "siteid" = ?
					'
				),
				'insert' => array(
					'ansi' => '
						INSERT INTO "mshop_index_price" (
							"prodid", "siteid", "priceid", "currencyid", "listtype",
							"type", "value", "costs", "rebate", "taxrate", "quantity",
							"mtime", "editor", "ctime"
						) VALUES (
							?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
						)
					',
					'pgsql' => '
						INSERT INTO "mshop_index_price" (
							"prodid", "siteid", "priceid", "currencyid", "listtype",
							"type", "value", "costs", "rebate", "taxrate", "quantity",
							"mtime", "editor", "ctime"
						) VALUES (
							?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
						)
						ON CONFLICT DO NOTHING
					'
				),
				'search' => array(
					'ansi' => '
						SELECT mpro."id"
						FROM "mshop_product" AS mpro
						:joins
						WHERE :cond
						GROUP BY mpro."id" /*-columns*/ , :columns /*columns-*/
						/*-orderby*/ ORDER BY :order /*orderby-*/
						LIMIT :size OFFSET :start
					'
				),
				'count' => array(
					'ansi' => '
						SELECT COUNT(*) AS "count"
						FROM (
							SELECT DISTINCT mpro."id"
							FROM "mshop_product" AS mpro
							:joins
							WHERE :cond
							LIMIT 1000 OFFSET 0
						) AS list
					'
				),
				'cleanup' => array(
					'ansi' => '
						DELETE FROM "mshop_index_price"
						WHERE "ctime" < ? AND "siteid" = ?
					'
				),
				'optimize' => array(
					'mysql' => array(
						'OPTIMIZE TABLE "mshop_index_price"',
					),
					'pgsql' => [],
				),
			),
		),
		'text' => array(
			'standard' => array(
				'delete' => array(
					'ansi' => '
						DELETE FROM "mshop_index_text"
						WHERE :cond AND "siteid" = ?
					'
				),
				'insert' => array(
					'ansi' => '
						INSERT INTO "mshop_index_text" (
							"prodid", "siteid", "textid", "langid", "listtype", "type",
							"domain", "value", "mtime", "editor", "ctime"
						) VALUES (
							?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
						)
					',
					'pgsql' => '
						INSERT INTO "mshop_index_text" (
							"prodid", "siteid", "textid", "langid", "listtype", "type",
							"domain", "value", "mtime", "editor", "ctime"
						) VALUES (
							?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
						)
						ON CONFLICT DO NOTHING
					'
				),
				'search' => array(
					'ansi' => '
						SELECT mpro."id"
						FROM "mshop_product" AS mpro
						:joins
						WHERE :cond
						GROUP BY mpro."id" /*-columns*/ , :columns /*columns-*/
						/*-orderby*/ ORDER BY :order /*orderby-*/
						LIMIT :size OFFSET :start
					'
				),
				'count' => array(
					'ansi' => '
						SELECT COUNT(*) AS "count"
						FROM (
							SELECT DISTINCT mpro."id"
							FROM "mshop_product" AS mpro
							:joins
							WHERE :cond
							LIMIT 1000 OFFSET 0
						) AS list
					'
				),
				'cleanup' => array(
					'ansi' => '
						DELETE FROM "mshop_index_text"
						WHERE "ctime" < ? AND "siteid" = ?
					'
				),
				'text' => array(
					'ansi' => '
						SELECT DISTINCT mindte."prodid", mindte."value"
						FROM "mshop_index_text" AS mindte
						JOIN "mshop_product" AS mpro ON mpro."id" = mindte."prodid"
						WHERE :cond
						/*-orderby*/ ORDER BY :order /*orderby-*/
						LIMIT :size OFFSET :start
					',
				),
				'optimize' => array(
					'mysql' => array(
						'OPTIMIZE TABLE "mshop_index_text"',
					),
					'pgsql' => [],
				),
			),
		),
		'standard' => array(
			'aggregate' => array(
				'ansi' => '
				SELECT "key", COUNT("id") AS "count"
				FROM (
					SELECT DISTINCT :key AS "key", mpro."id" AS "id"
					FROM "mshop_product" AS mpro
					:joins
					WHERE :cond
					/*-orderby*/ ORDER BY :order /*orderby-*/
					LIMIT :size OFFSET :start
				) AS list
				GROUP BY "key"
			'
			),
			'search' => array(
				'ansi' => '
					SELECT mpro."id"
					FROM "mshop_product" AS mpro
					:joins
					WHERE :cond
					GROUP BY mpro."id" /*-columns*/ , :columns /*columns-*/
					/*-orderby*/ ORDER BY :order /*orderby-*/
					LIMIT :size OFFSET :start
				'
			),
			'count' => array(
				'ansi' => '
					SELECT COUNT(*) AS "count"
					FROM (
						SELECT DISTINCT mpro."id"
						FROM "mshop_product" AS mpro
						:joins
						WHERE :cond
						LIMIT 1000 OFFSET 0
					) AS list
				'
			),
			'optimize' => array(
				'mysql' => array(
					'ANALYZE TABLE "mshop_product"',
					'ANALYZE TABLE "mshop_product_list"',
				),
				'pgsql' => [],
			),
		),
	),
);