<?php

/**
 * @copyright Aimeos (aimeos.org), 2015
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @package MShop
 * @subpackage Supplier
 */


namespace Aimeos\MShop\Supplier\Manager\Lists;


/**
 * Default Supplier list manager for creating and handling Supplier list items.
 * @package MShop
 * @subpackage Supplier
 */
class Standard
	extends \Aimeos\MShop\Common\Manager\Lists\Base
	implements \Aimeos\MShop\Supplier\Manager\Lists\Iface
{
	private $searchConfig = array(
		'supplier.lists.id'=> array(
			'code'=>'supplier.lists.id',
			'internalcode'=>'msupli."id"',
			'internaldeps' => array( 'LEFT JOIN "mshop_supplier_list" AS msupli ON ( msup."id" = msupli."parentid" )' ),
			'label'=>'Supplier list ID',
			'type'=> 'integer',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_INT,
			'public' => false,
		),
		'supplier.lists.siteid'=> array(
			'code'=>'supplier.lists.siteid',
			'internalcode'=>'msupli."siteid"',
			'label'=>'Supplier list site ID',
			'type'=> 'integer',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_INT,
			'public' => false,
		),
		'supplier.lists.parentid'=> array(
			'code'=>'supplier.lists.parentid',
			'internalcode'=>'msupli."parentid"',
			'label'=>'Supplier list parent ID',
			'type'=> 'integer',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_INT,
			'public' => false,
		),
		'supplier.lists.domain'=> array(
			'code'=>'supplier.lists.domain',
			'internalcode'=>'msupli."domain"',
			'label'=>'Supplier list domain',
			'type'=> 'string',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'supplier.lists.typeid' => array(
			'code'=>'supplier.lists.typeid',
			'internalcode'=>'msupli."typeid"',
			'label'=>'Supplier list type ID',
			'type'=> 'integer',
			'internaltype' => \Aimeos\MW\DB\Statement\Base::PARAM_INT,
			'public' => false,
		),
		'supplier.lists.refid'=> array(
			'code'=>'supplier.lists.refid',
			'internalcode'=>'msupli."refid"',
			'label'=>'Supplier list reference ID',
			'type'=> 'string',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'supplier.lists.datestart' => array(
			'code'=>'supplier.lists.datestart',
			'internalcode'=>'msupli."start"',
			'label'=>'Supplier list start date/time',
			'type'=> 'datetime',
			'internaltype' => \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'supplier.lists.dateend' => array(
			'code'=>'supplier.lists.dateend',
			'internalcode'=>'msupli."end"',
			'label'=>'Supplier list end date/time',
			'type'=> 'datetime',
			'internaltype' => \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'supplier.lists.config' => array(
			'code'=>'supplier.lists.config',
			'internalcode'=>'msupli."config"',
			'label'=>'Supplier list config',
			'type'=> 'string',
			'internaltype' => \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'supplier.lists.position' => array(
			'code'=>'supplier.lists.position',
			'internalcode'=>'msupli."pos"',
			'label'=>'Supplier list position',
			'type'=> 'integer',
			'internaltype' => \Aimeos\MW\DB\Statement\Base::PARAM_INT,
		),
		'supplier.lists.status' => array(
			'code'=>'supplier.lists.status',
			'internalcode'=>'msupli."status"',
			'label'=>'Supplier list status',
			'type'=> 'integer',
			'internaltype' => \Aimeos\MW\DB\Statement\Base::PARAM_INT,
		),
		'supplier.lists.ctime'=> array(
			'code'=>'supplier.lists.ctime',
			'internalcode'=>'msupli."ctime"',
			'label'=>'Supplier list create date/time',
			'type'=> 'datetime',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'supplier.lists.mtime'=> array(
			'code'=>'supplier.lists.mtime',
			'internalcode'=>'msupli."mtime"',
			'label'=>'Supplier list modification date/time',
			'type'=> 'datetime',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
		'supplier.lists.editor'=> array(
			'code'=>'supplier.lists.editor',
			'internalcode'=>'msupli."editor"',
			'label'=>'Supplier list editor',
			'type'=> 'string',
			'internaltype'=> \Aimeos\MW\DB\Statement\Base::PARAM_STR,
		),
	);


	/**
	 * Initializes the object.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );
		$this->setResourceName( 'db-supplier' );
	}


	/**
	 * Removes old entries from the storage.
	 *
	 * @param array $siteids List of IDs for sites whose entries should be deleted
	 */
	public function cleanup( array $siteids )
	{
		$path = 'mshop/supplier/manager/lists/submanagers';
		foreach( $this->getContext()->getConfig()->get( $path, array( 'type' ) ) as $domain ) {
			$this->getSubManager( $domain )->cleanup( $siteids );
		}

		$this->cleanupBase( $siteids, 'mshop/supplier/manager/lists/standard/delete' );
	}


	/**
	 * Returns the list attributes that can be used for searching.
	 *
	 * @param boolean $withsub Return also attributes of sub-managers if true
	 * @return array List of attribute items implementing \Aimeos\MW\Common\Criteria\Attribute\Iface
	 */
	public function getSearchAttributes( $withsub = true )
	{
		/** mshop/supplier/manager/lists/submanagers
		 * List of manager names that can be instantiated by the Supplier list manager
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
		$path = 'mshop/supplier/manager/lists/submanagers';

		return $this->getSearchAttributesBase( $this->searchConfig, $path, array( 'type' ), $withsub );
	}


	/**
	 * Returns a new manager for Supplier list extensions.
	 *
	 * @param string $manager Name of the sub manager type in lower case
	 * @param string|null $name Name of the implementation, will be from configuration (or Default) if null
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager for different extensions, e.g types, lists etc.
	 */
	public function getSubManager( $manager, $name = null )
	{
		/** mshop/supplier/manager/lists/name
		 * Class name of the used Supplier list manager implementation
		 *
		 * Each default Supplier list manager can be replaced by an alternative imlementation.
		 * To use this implementation, you have to set the last part of the class
		 * name as configuration value so the manager factory knows which class it
		 * has to instantiate.
		 *
		 * For example, if the name of the default class is
		 *
		 *  \Aimeos\MShop\Supplier\Manager\Lists\Standard
		 *
		 * and you want to replace it with your own version named
		 *
		 *  \Aimeos\MShop\Supplier\Manager\Lists\Mylist
		 *
		 * then you have to set the this configuration option:
		 *
		 *  mshop/supplier/manager/lists/name = Mylist
		 *
		 * The value is the last part of your own class name and it's case sensitive,
		 * so take care that the configuration value is exactly named like the last
		 * part of the class name.
		 *
		 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
		 * characters are possible! You should always start the last part of the class
		 * name with an upper case character and continue only with lower case characters
		 * or numbers. Avoid chamel case names like "MyList"!
		 *
		 * @param string Last part of the class name
		 * @since 2014.03
		 * @category Developer
		 */

		/** mshop/supplier/manager/lists/decorators/excludes
		 * Excludes decorators added by the "common" option from the Supplier list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "mshop/common/manager/decorators/default" before they are wrapped
		 * around the Supplier list manager.
		 *
		 *  mshop/supplier/manager/lists/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\MShop\Common\Manager\Decorator\*") added via
		 * "mshop/common/manager/decorators/default" for the Supplier list manager.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/supplier/manager/lists/decorators/global
		 * @see mshop/supplier/manager/lists/decorators/local
		 */

		/** mshop/supplier/manager/lists/decorators/global
		 * Adds a list of globally available decorators only to the Supplier list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\MShop\Common\Manager\Decorator\*") around the Supplier list manager.
		 *
		 *  mshop/supplier/manager/lists/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\MShop\Common\Manager\Decorator\Decorator1" only to the supplier controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/supplier/manager/lists/decorators/excludes
		 * @see mshop/supplier/manager/lists/decorators/local
		 */

		/** mshop/supplier/manager/lists/decorators/local
		 * Adds a list of local decorators only to the Supplier list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\MShop\Common\Manager\Decorator\*") around the Supplier list manager.
		 *
		 *  mshop/supplier/manager/lists/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\MShop\Common\Manager\Decorator\Decorator2" only to the supplier
		 * controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/supplier/manager/lists/decorators/excludes
		 * @see mshop/supplier/manager/lists/decorators/global
		 */

		return $this->getSubManagerBase( 'supplier', 'lists/' . $manager, $name );
	}


	/**
	 * Returns the config path for retrieving the configuration values.
	 *
	 * @return string Configuration path
	 */
	protected function getConfigPath()
	{
		/** mshop/supplier/manager/lists/standard/insert
		 * Inserts a new Supplier list record into the database table
		 *
		 * Items with no ID yet (i.e. the ID is NULL) will be created in
		 * the database and the newly created ID retrieved afterwards
		 * using the "newid" SQL statement.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding
		 * the values from the Supplier list item to the statement before they are
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
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/move
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/update
		 * Updates an existing Supplier list record in the database
		 *
		 * Items which already have an ID (i.e. the ID is not NULL) will
		 * be updated in the database.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding
		 * the values from the Supplier list item to the statement before they are
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
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/move
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/newid
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
		 *  SELECT currval('seq_msupli_id')
		 * For SQL Server:
		 *  SELECT SCOPE_IDENTITY()
		 * For Oracle:
		 *  SELECT "seq_msupli_id".CURRVAL FROM DUAL
		 *
		 * There's no way to retrive the new ID by a SQL statements that
		 * fits for most database servers as they implement their own
		 * specific way.
		 *
		 * @param string SQL statement for retrieving the last inserted record ID
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/move
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/delete
		 * Deletes the items matched by the given IDs from the database
		 *
		 * Removes the records specified by the given IDs from the supplier database.
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
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/move
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/search
		 * Retrieves the records matched by the given criteria in the database
		 *
		 * Fetches the records matched by the given criteria from the supplier
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
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/move
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/count
		 * Counts the number of records matched by the given criteria in the database
		 *
		 * Counts all records matched by the given criteria from the supplier
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
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/move
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/aggregate
		 * Counts the number of records grouped by the values in the key column and matched by the given criteria
		 *
		 * Groups all records by the values in the key column and counts their
		 * occurence. The matched records can be limited by the given criteria
		 * from the order database. The records must be from one of the sites
		 * that are configured via the context item. If the current site is part
		 * of a tree of sites, the statement can count all records from the
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
		 * This statement doesn't return any records. Instead, it returns pairs
		 * of the different values found in the key column together with the
		 * number of records that have been found for that key values.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * compatible with most relational database systems. This also
		 * includes using double quotes for table and column names.
		 *
		 * @param string SQL statement for aggregating order items
		 * @since 2014.07
		 * @category Developer
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/move
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/getposmax
		 * Retrieves the position of the list record with the highest number
		 *
		 * When moving or inserting records into the list, the highest position
		 * number must be known to append records at the end. Only records from
		 * the same site that is configured via the consupplier item are considered.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding the
		 * required values to the statement before they are sent to the
		 * database server. The number of question marks must be the same as
		 * used in the moveItem() method and their order must correspond to the
		 * order in the same method.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * with most relational database systems. This also includes using
		 * double quotes for table and column names.
		 *
		 * @param string SQL statement for determining the position with the highest number
		 * @since 2014.07
		 * @category Developer
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/move
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/move
		 * Moves a list item to another position and updates the other items accordingly
		 *
		 * Reorders the records in the list table by updating their position
		 * field. The records must be from the site that is configured via the
		 * consupplier item.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding the
		 * required values to the statement before they are sent to the
		 * database server. The number of question marks must be the same as
		 * used in the moveItem() method and their order must correspond to the
		 * order in the same method.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * with most relational database systems. This also includes using
		 * double quotes for table and column names.
		 *
		 * @param string SQL statement for moving items
		 * @since 2014.07
		 * @category Developer
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/updatepos
		 */

		/** mshop/supplier/manager/lists/standard/updatepos
		 * Updates the position value of a single list record
		 *
		 * The moveItem() method needs to set the position value of a sinlge
		 * record in some cases. The records must be from the site that is
		 * configured via the consupplier item.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding the
		 * required values to the statement before they are sent to the
		 * database server. The number of question marks must be the same as
		 * used in the moveItem() method and their order must correspond to the
		 * order in the same method.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * with most relational database systems. This also includes using
		 * double quotes for table and column names.
		 *
		 * @param string SQL statement for moving items
		 * @since 2014.07
		 * @category Developer
		 * @see mshop/supplier/manager/lists/standard/insert
		 * @see mshop/supplier/manager/lists/standard/update
		 * @see mshop/supplier/manager/lists/standard/newid
		 * @see mshop/supplier/manager/lists/standard/delete
		 * @see mshop/supplier/manager/lists/standard/search
		 * @see mshop/supplier/manager/lists/standard/count
		 * @see mshop/supplier/manager/lists/standard/aggregate
		 * @see mshop/supplier/manager/lists/standard/getposmax
		 * @see mshop/supplier/manager/lists/standard/move
		 */

		return 'mshop/supplier/manager/lists/standard/';
	}


	/**
	 * Returns the search configuration for searching items.
	 *
	 * @return array Associative list of search keys and search definitions
	 */
	protected function getSearchConfig()
	{
		return $this->searchConfig;
	}
}