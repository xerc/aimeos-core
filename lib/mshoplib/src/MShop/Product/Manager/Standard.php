<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2011
 * @copyright Aimeos (aimeos.org), 2015
 * @package MShop
 * @subpackage Product
 */


namespace Aimeos\MShop\Product\Manager;


/**
 * Default product manager.
 *
 * @package MShop
 * @subpackage Product
 */
class Standard
	extends \Aimeos\MShop\Common\Manager\ListRef\Base
	implements \Aimeos\MShop\Product\Manager\Iface
{
	private $searchConfig = array(
		'product.id'=> array(
			'code'=>'product.id',
			'internalcode'=>'mpro."id"',
			'label'=>'Product ID',
			'type'=> 'integer',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_INT,
		),
		'product.siteid'=> array(
			'code'=>'product.siteid',
			'internalcode'=>'mpro."siteid"',
			'label'=>'Product site ID',
			'type'=> 'integer',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_INT,
			'public' => false,
		),
		'product.typeid'=> array(
			'code'=>'product.typeid',
			'internalcode'=>'mpro."typeid"',
			'label'=>'Product type ID',
			'type'=> 'integer',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_INT,
			'public' => false,
		),
		'product.code'=> array(
			'code'=>'product.code',
			'internalcode'=>'mpro."code"',
			'label'=>'Product code',
			'type'=> 'string',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.label'=> array(
			'code'=>'product.label',
			'internalcode'=>'mpro."label"',
			'label'=>'Product label',
			'type'=> 'string',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.config' => array(
			'code' => 'product.config',
			'internalcode' => 'mpro."config"',
			'label' => 'Product config',
			'type' => 'string',
			'internaltype' => \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.suppliercode'=> array(
			'code'=>'product.suppliercode',
			'internalcode'=>'mpro."suppliercode"',
			'label'=>'Product supplier code',
			'type'=> 'string',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.datestart'=> array(
			'code'=>'product.datestart',
			'internalcode'=>'mpro."start"',
			'label'=>'Product start date/time',
			'type'=> 'datetime',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.dateend'=> array(
			'code'=>'product.dateend',
			'internalcode'=>'mpro."end"',
			'label'=>'Product end date/time',
			'type'=> 'datetime',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.ctime'=> array(
			'code'=>'product.ctime',
			'internalcode'=>'mpro."ctime"',
			'label'=>'Product create date/time',
			'type'=> 'datetime',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.mtime'=> array(
			'code'=>'product.mtime',
			'internalcode'=>'mpro."mtime"',
			'label'=>'Product modification date/time',
			'type'=> 'datetime',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.editor'=> array(
			'code'=>'product.editor',
			'internalcode'=>'mpro."editor"',
			'label'=>'Product editor',
			'type'=> 'string',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'product.status'=> array(
			'code'=>'product.status',
			'internalcode'=>'mpro."status"',
			'label'=>'Product status',
			'type'=> 'integer',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_INT,
		),
		'product.contains' => array(
			'code'=>'product.contains()',
			'internalcode'=>'',
			'label'=>'Number of product list items, parameter(<domain>,<list type ID>,<reference IDs>)',
			'type'=> 'integer',
			'internaltype' => \Aimeos\MW\DB\Statement\Base::PARAM_INT,
			'public' => false,
		),
	);


	/**
	 * Creates the product manager that will use the given context object.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object with required objects
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );
		$this->setResourceName( 'db-product' );

		$date = date( 'Y-m-d H:i:00' );

		$this->searchConfig['product.contains']['internalcode'] =
			'( SELECT COUNT(mproli2."parentid") FROM "mshop_product_list" AS mproli2
				WHERE mpro."id" = mproli2."parentid" AND :site
					AND mproli2."domain" = $1 AND mproli2."refid" IN ( $3 ) AND mproli2."typeid" = $2
					AND ( mproli2."start" IS NULL OR mproli2."start" <= \'' . $date . '\' )
					AND ( mproli2."end" IS NULL OR mproli2."end" >= \'' . $date . '\' ) )';

		$sites = $context->getLocale()->getSitePath();
		$this->replaceSiteMarker( $this->searchConfig['product.contains'], 'mproli2."siteid"', $sites, ':site' );
	}


	/**
	 * Removes old entries from the storage.
	 *
	 * @param integer[] $siteids List of IDs for sites whose entries should be deleted
	 */
	public function cleanup( array $siteids )
	{
		$path = 'mshop/product/manager/submanagers';
		$default = array( 'type', 'property', 'stock', 'lists' );

		foreach( $this->getContext()->getConfig()->get( $path, $default ) as $domain ) {
			$this->getSubManager( $domain )->cleanup( $siteids );
		}

		$this->cleanupBase( $siteids, 'mshop/product/manager/standard/delete' );
	}


	/**
	 * Create new product item object.
	 *
	 * @return \Aimeos\MShop\Product\Item\Iface
	 */
	public function createItem()
	{
		$values = array( 'siteid' => $this->getContext()->getLocale()->getSiteId() );
		return $this->createItemBase( $values );
	}


	/**
	 * Adds a new product to the storage.
	 *
	 * @param \Aimeos\MShop\Common\Item\Iface $item Product item that should be saved to the storage
	 * @param boolean $fetch True if the new ID should be returned in the item
	 */
	public function saveItem( \Aimeos\MShop\Common\Item\Iface $item, $fetch = true )
	{
		$iface = '\\Aimeos\\MShop\\Product\\Item\\Iface';
		if( !( $item instanceof $iface ) ) {
			throw new \Aimeos\MShop\Product\Exception( sprintf( 'Object is not of required type "%1$s"', $iface ) );
		}

		if( !$item->isModified() ) { return; }

		$context = $this->getContext();

		$dbm = $context->getDatabaseManager();
		$dbname = $this->getResourceName();
		$conn = $dbm->acquire( $dbname );

		try
		{
			$id = $item->getId();
			$date = date( 'Y-m-d H:i:s' );

			if( $id === null )
			{
				/** mshop/product/manager/standard/insert
				 * Inserts a new product record into the database table
				 *
				 * Items with no ID yet (i.e. the ID is NULL) will be created in
				 * the database and the newly created ID retrieved afterwards
				 * using the "newid" SQL statement.
				 *
				 * The SQL statement must be a string suitable for being used as
				 * prepared statement. It must include question marks for binding
				 * the values from the product item to the statement before they are
				 * sent to the database server. The number of question marks must
				 * be the same as the number of columns listed in the INSERT
				 * statement. The order of the columns must correspond to the
				 * order in the saveItems() method, so the correct values are
				 * bound to the columns.
				 *
				 * The SQL statement should conform to the ANSI standard to be
				 * compatible with most relational database systems. This also
				 * includes using double quotes for table and column names.
				 *
				 * @param string SQL statement for inserting records
				 * @since 2014.03
				 * @category Developer
				 * @see mshop/product/manager/standard/update
				 * @see mshop/product/manager/standard/newid
				 * @see mshop/product/manager/standard/delete
				 * @see mshop/product/manager/standard/search
				 * @see mshop/product/manager/standard/count
				 */
				$path = 'mshop/product/manager/standard/insert';
			}
			else
			{
				/** mshop/product/manager/standard/update
				 * Updates an existing product record in the database
				 *
				 * Items which already have an ID (i.e. the ID is not NULL) will
				 * be updated in the database.
				 *
				 * The SQL statement must be a string suitable for being used as
				 * prepared statement. It must include question marks for binding
				 * the values from the product item to the statement before they are
				 * sent to the database server. The order of the columns must
				 * correspond to the order in the saveItems() method, so the
				 * correct values are bound to the columns.
				 *
				 * The SQL statement should conform to the ANSI standard to be
				 * compatible with most relational database systems. This also
				 * includes using double quotes for table and column names.
				 *
				 * @param string SQL statement for updating records
				 * @since 2014.03
				 * @category Developer
				 * @see mshop/product/manager/standard/insert
				 * @see mshop/product/manager/standard/newid
				 * @see mshop/product/manager/standard/delete
				 * @see mshop/product/manager/standard/search
				 * @see mshop/product/manager/standard/count
				 */
				$path = 'mshop/product/manager/standard/update';
			}

			$stmt = $this->getCachedStatement( $conn, $path );
			$stmt->bind( 1, $context->getLocale()->getSiteId(), \Aimeos\MW\DB\Statement\Base::PARAM_INT );
			$stmt->bind( 2, $item->getTypeId(), \Aimeos\MW\DB\Statement\Base::PARAM_INT );
			$stmt->bind( 3, $item->getCode() );
			$stmt->bind( 4, $item->getSupplierCode() );
			$stmt->bind( 5, $item->getLabel() );
			$stmt->bind( 6, $item->getStatus(), \Aimeos\MW\DB\Statement\Base::PARAM_INT );
			$stmt->bind( 7, $item->getDateStart() );
			$stmt->bind( 8, $item->getDateEnd() );
			$stmt->bind( 9, json_encode( $item->getConfig() ) );
			$stmt->bind( 10, $date ); // mtime
			$stmt->bind( 11, $context->getEditor() );

			if( $id !== null ) {
				$stmt->bind( 12, $id, \Aimeos\MW\DB\Statement\Base::PARAM_INT );
				$item->setId( $id ); //so item is no longer modified
			} else {
				$stmt->bind( 12, $date ); // ctime
			}

			$stmt->execute()->finish();

			if( $id === null && $fetch === true )
			{
				/** mshop/product/manager/standard/newid
				 * Retrieves the ID generated by the database when inserting a new record
				 *
				 * As soon as a new record is inserted into the database table,
				 * the database server generates a new and unique identifier for
				 * that record. This ID can be used for retrieving, updating and
				 * deleting that specific record from the table again.
				 *
				 * For MySQL:
				 *  SELECT LAST_INSERT_ID()
				 * For PostgreSQL:
				 *  SELECT currval('seq_mpro_id')
				 * For SQL Server:
				 *  SELECT SCOPE_IDENTITY()
				 * For Oracle:
				 *  SELECT "seq_mpro_id".CURRVAL FROM DUAL
				 *
				 * There's no way to retrive the new ID by a SQL statements that
				 * fits for most database servers as they implement their own
				 * specific way.
				 *
				 * @param string SQL statement for retrieving the last inserted record ID
				 * @since 2014.03
				 * @category Developer
				 * @see mshop/product/manager/standard/insert
				 * @see mshop/product/manager/standard/update
				 * @see mshop/product/manager/standard/delete
				 * @see mshop/product/manager/standard/search
				 * @see mshop/product/manager/standard/count
				 */
				$path = 'mshop/product/manager/standard/newid';
				$item->setId( $this->newId( $conn, $path ) );
			}

			$dbm->release( $conn, $dbname );
		}
		catch( \Exception $e )
		{
			$dbm->release( $conn, $dbname );
			throw $e;
		}
	}


	/**
	 * Removes multiple items specified by ids in the array.
	 *
	 * @param array $ids List of IDs
	 */
	public function deleteItems( array $ids )
	{
		/** mshop/product/manager/standard/delete
		 * Deletes the items matched by the given IDs from the database
		 *
		 * Removes the records specified by the given IDs from the product database.
		 * The records must be from the site that is configured via the
		 * context item.
		 *
		 * The ":cond" placeholder is replaced by the name of the ID column and
		 * the given ID or list of IDs while the site ID is bound to the question
		 * mark.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * compatible with most relational database systems. This also
		 * includes using double quotes for table and column names.
		 *
		 * @param string SQL statement for deleting items
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/product/manager/standard/insert
		 * @see mshop/product/manager/standard/update
		 * @see mshop/product/manager/standard/newid
		 * @see mshop/product/manager/standard/search
		 * @see mshop/product/manager/standard/count
		 */
		$path = 'mshop/product/manager/standard/delete';
		$this->deleteItemsBase( $ids, $path );
	}


	/**
	 * Returns the product item for the given product ID.
	 *
	 * @param integer $id Unique ID of the product item
	 * @param string[] $ref List of domains to fetch list items and referenced items for
	 * @return \Aimeos\MShop\Product\Item\Iface Returns the product item of the given id
	 * @throws \Aimeos\MShop\Exception If item couldn't be found
	 */
	public function getItem( $id, array $ref = array() )
	{
		return $this->getItemBase( 'product.id', $id, $ref );
	}


	/**
	 * Search for products based on the given criteria.
	 *
	 * @param \Aimeos\MW\Common\Criteria\Iface $search Search object containing the conditions
	 * @param array $ref List of domains to fetch list items and referenced items for
	 * @param integer &$total Number of items that are available in total
	 *
	 * @return array List of products implementing \Aimeos\MShop\Product\Item\Iface
	 */
	public function searchItems( \Aimeos\MW\Common\Criteria\Iface $search, array $ref = array(), &$total = null )
	{
		$map = $typeIds = array();
		$context = $this->getContext();

		$dbm = $context->getDatabaseManager();
		$dbname = $this->getResourceName();
		$conn = $dbm->acquire( $dbname );

		try
		{
			$required = array( 'product' );
			$level = \Aimeos\MShop\Locale\Manager\Base::SITE_ALL;

			/** mshop/product/manager/standard/search
			 * Retrieves the records matched by the given criteria in the database
			 *
			 * Fetches the records matched by the given criteria from the product
			 * database. The records must be from one of the sites that are
			 * configured via the context item. If the current site is part of
			 * a tree of sites, the SELECT statement can retrieve all records
			 * from the current site and the complete sub-tree of sites.
			 *
			 * As the records can normally be limited by criteria from sub-managers,
			 * their tables must be joined in the SQL context. This is done by
			 * using the "internaldeps" property from the definition of the ID
			 * column of the sub-managers. These internal dependencies specify
			 * the JOIN between the tables and the used columns for joining. The
			 * ":joins" placeholder is then replaced by the JOIN strings from
			 * the sub-managers.
			 *
			 * To limit the records matched, conditions can be added to the given
			 * criteria object. It can contain comparisons like column names that
			 * must match specific values which can be combined by AND, OR or NOT
			 * operators. The resulting string of SQL conditions replaces the
			 * ":cond" placeholder before the statement is sent to the database
			 * server.
			 *
			 * If the records that are retrieved should be ordered by one or more
			 * columns, the generated string of column / sort direction pairs
			 * replaces the ":order" placeholder. In case no ordering is required,
			 * the complete ORDER BY part including the "\/*-orderby*\/...\/*orderby-*\/"
			 * markers is removed to speed up retrieving the records. Columns of
			 * sub-managers can also be used for ordering the result set but then
			 * no index can be used.
			 *
			 * The number of returned records can be limited and can start at any
			 * number between the begining and the end of the result set. For that
			 * the ":size" and ":start" placeholders are replaced by the
			 * corresponding values from the criteria object. The default values
			 * are 0 for the start and 100 for the size value.
			 *
			 * The SQL statement should conform to the ANSI standard to be
			 * compatible with most relational database systems. This also
			 * includes using double quotes for table and column names.
			 *
			 * @param string SQL statement for searching items
			 * @since 2014.03
			 * @category Developer
			 * @see mshop/product/manager/standard/insert
			 * @see mshop/product/manager/standard/update
			 * @see mshop/product/manager/standard/newid
			 * @see mshop/product/manager/standard/delete
			 * @see mshop/product/manager/standard/count
			 */
			$cfgPathSearch = 'mshop/product/manager/standard/search';

			/** mshop/product/manager/standard/count
			 * Counts the number of records matched by the given criteria in the database
			 *
			 * Counts all records matched by the given criteria from the product
			 * database. The records must be from one of the sites that are
			 * configured via the context item. If the current site is part of
			 * a tree of sites, the statement can count all records from the
			 * current site and the complete sub-tree of sites.
			 *
			 * As the records can normally be limited by criteria from sub-managers,
			 * their tables must be joined in the SQL context. This is done by
			 * using the "internaldeps" property from the definition of the ID
			 * column of the sub-managers. These internal dependencies specify
			 * the JOIN between the tables and the used columns for joining. The
			 * ":joins" placeholder is then replaced by the JOIN strings from
			 * the sub-managers.
			 *
			 * To limit the records matched, conditions can be added to the given
			 * criteria object. It can contain comparisons like column names that
			 * must match specific values which can be combined by AND, OR or NOT
			 * operators. The resulting string of SQL conditions replaces the
			 * ":cond" placeholder before the statement is sent to the database
			 * server.
			 *
			 * Both, the strings for ":joins" and for ":cond" are the same as for
			 * the "search" SQL statement.
			 *
			 * Contrary to the "search" statement, it doesn't return any records
			 * but instead the number of records that have been found. As counting
			 * thousands of records can be a long running task, the maximum number
			 * of counted records is limited for performance reasons.
			 *
			 * The SQL statement should conform to the ANSI standard to be
			 * compatible with most relational database systems. This also
			 * includes using double quotes for table and column names.
			 *
			 * @param string SQL statement for counting items
			 * @since 2014.03
			 * @category Developer
			 * @see mshop/product/manager/standard/insert
			 * @see mshop/product/manager/standard/update
			 * @see mshop/product/manager/standard/newid
			 * @see mshop/product/manager/standard/delete
			 * @see mshop/product/manager/standard/search
			 */
			$cfgPathCount = 'mshop/product/manager/standard/count';

			$results = $this->searchItemsBase( $conn, $search, $cfgPathSearch, $cfgPathCount, $required, $total, $level );

			while( ( $row = $results->fetch() ) !== false )
			{
				$config = $row['config'];

				if( $config && ( $row['config'] = json_decode( $config, true ) ) === null )
				{
					$msg = sprintf( 'Invalid JSON as result of search for ID "%2$s" in "%1$s": %3$s', 'mshop_product.config', $row['id'], $config );
					$this->getContext()->getLogger()->log( $msg, \Aimeos\MW\Logger\Base::WARN );
				}

				$map[$row['id']] = $row;
				$typeIds[$row['typeid']] = null;
			}

			$dbm->release( $conn, $dbname );
		}
		catch( \Exception $e )
		{
			$dbm->release( $conn, $dbname );
			throw $e;
		}

		if( !empty( $typeIds ) )
		{
			$typeManager = $this->getSubManager( 'type' );
			$typeSearch = $typeManager->createSearch();
			$typeSearch->setConditions( $typeSearch->compare( '==', 'product.type.id', array_keys( $typeIds ) ) );
			$typeSearch->setSlice( 0, $search->getSliceSize() );
			$typeItems = $typeManager->searchItems( $typeSearch );

			foreach( $map as $id => $row )
			{
				if( isset( $typeItems[$row['typeid']] ) ) {
					$map[$id]['type'] = $typeItems[$row['typeid']]->getCode();
				}
			}
		}

		return $this->buildItems( $map, $ref, 'product' );
	}


	/**
	 * Returns the attributes that can be used for searching.
	 *
	 * @param boolean $withsub Return also attributes of sub-managers if true
	 * @return array Returns a list of attribtes implementing \Aimeos\MW\Common\Criteria\Attribute\Iface
	 */
	public function getSearchAttributes( $withsub = true )
	{
		/** mshop/product/manager/submanagers
		 * List of manager names that can be instantiated by the product manager
		 *
		 * Managers provide a generic interface to the underlying storage.
		 * Each manager has or can have sub-managers caring about particular
		 * aspects. Each of these sub-managers can be instantiated by its
		 * parent manager using the getSubManager() method.
		 *
		 * The search keys from sub-managers can be normally used in the
		 * manager as well. It allows you to search for items of the manager
		 * using the search keys of the sub-managers to further limit the
		 * retrieved list of items.
		 *
		 * @param array List of sub-manager names
		 * @since 2014.03
		 * @category Developer
		 */
		$path = 'mshop/product/manager/submanagers';
		$default = array( 'type', 'property', 'lists', 'stock' );

		return $this->getSearchAttributesBase( $this->searchConfig, $path, $default, $withsub );
	}


	/**
	 * Returns a new manager for product extensions.
	 *
	 * @param string $manager Name of the sub manager type in lower case
	 * @param string|null $name Name of the implementation, will be from configuration (or Default) if null
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager for different extensions, e.g stock, tags, locations, etc.
	 */
	public function getSubManager( $manager, $name = null )
	{
		return $this->getSubManagerBase( 'product', $manager, $name );
	}


	/**
	 * Creates a search object and optionally sets base criteria.
	 *
	 * @param boolean $default Add default criteria
	 * @return \Aimeos\MW\Common\Criteria\Iface Criteria object
	 */
	public function createSearch( $default = false )
	{
		if( $default === true )
		{
			$curDate = date( 'Y-m-d H:i:00', time() );
			$object = $this->createSearchBase( 'product' );

			$expr = array( $object->getConditions() );

			$temp = array(
				$object->compare( '==', 'product.datestart', null ),
				$object->compare( '<=', 'product.datestart', $curDate ),
			);
			$expr[] = $object->combine( '||', $temp );

			$temp = array(
				$object->compare( '==', 'product.dateend', null ),
				$object->compare( '>=', 'product.dateend', $curDate ),
			);
			$expr[] = $object->combine( '||', $temp );

			$object->setConditions( $object->combine( '&&', $expr ) );

			return $object;
		}

		return parent::createSearch();
	}


	/**
	 * Create new product item object initialized with given parameters.
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $item Product item object
	 * @return array Associative list of key/value pairs suitable for product item constructor
	 */
	protected function createArray( \Aimeos\MShop\Product\Item\Iface $item )
	{
		return array(
			'id' => $item->getId(),
			'typeid' => $item->getTypeId(),
			'type' => $item->getType(),
			'status' => $item->getStatus(),
			'label' => $item->getLabel(),
			'start' => $item->getDateStart(),
			'end' => $item->getDateEnd(),
			'code' => $item->getCode(),
			'suppliercode' => $item->getSupplierCode(),
			'ctime' => $item->getTimeCreated(),
			'mtime' => $item->getTimeModified(),
			'editor' => $item->getEditor(),
		);
	}


	/**
	 * Create new product item object initialized with given parameters.
	 *
	 * @param array $values Associative list of key/value pairs
	 * @param array $listitems List of items implementing \Aimeos\MShop\Common\Item\Lists\Iface
	 * @param array $refItems List of items implementing \Aimeos\MShop\Common\Item\Iface
	 * @return \Aimeos\MShop\Product\Item\Iface New product item
	 */
	protected function createItemBase( array $values = array(), array $listitems = array(), array $refItems = array() )
	{
		return new \Aimeos\MShop\Product\Item\Standard( $values, $listitems, $refItems );
	}
}