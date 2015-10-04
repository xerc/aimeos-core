<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @package MShop
 * @subpackage Text
 */


/**
 * Default text type manager for creating and handling text type items.
 * @package MShop
 * @subpackage Text
 */
class MShop_Text_Manager_Type_Standard
	extends MShop_Common_Manager_Type_Base
	implements MShop_Text_Manager_Type_Iface
{
	private $searchConfig = array(
		'text.type.id'=> array(
			'code'=>'text.type.id',
			'internalcode'=>'mtexty."id"',
			'internaldeps' => array( 'LEFT JOIN "mshop_text_type" AS mtexty ON ( mtex."typeid" = mtexty."id" )' ),
			'label'=>'Text type ID',
			'type'=> 'integer',
			'internaltype'=> MW_DB_Statement_Base::PARAM_INT,
			'public' => false,
		),
		'text.type.siteid'=> array(
			'code'=>'text.type.siteid',
			'internalcode'=>'mtexty."siteid"',
			'label'=>'Text type site ID',
			'type'=> 'integer',
			'internaltype'=> MW_DB_Statement_Base::PARAM_INT,
			'public' => false,
		),
		'text.type.code' => array(
			'code'=>'text.type.code',
			'internalcode'=>'mtexty."code"',
			'label'=>'Text type code',
			'type'=> 'string',
			'internaltype' => MW_DB_Statement_Base::PARAM_STR,
		),
		'text.type.domain' => array(
			'code'=>'text.type.domain',
			'internalcode'=>'mtexty."domain"',
			'label'=>'Text type domain',
			'type'=> 'string',
			'internaltype' => MW_DB_Statement_Base::PARAM_STR,
		),
		'text.type.label' => array(
			'code'=>'text.type.label',
			'internalcode'=>'mtexty."label"',
			'label'=>'Text type label',
			'type'=> 'string',
			'internaltype' => MW_DB_Statement_Base::PARAM_STR,
		),
		'text.type.status' => array(
			'code'=>'text.type.status',
			'internalcode'=>'mtexty."status"',
			'label'=>'Text type status',
			'type'=> 'integer',
			'internaltype' => MW_DB_Statement_Base::PARAM_INT,
		),
		'text.type.ctime'=> array(
			'code'=>'text.type.ctime',
			'internalcode'=>'mtexty."ctime"',
			'label'=>'Text type create date/time',
			'type'=> 'datetime',
			'internaltype'=> MW_DB_Statement_Base::PARAM_STR,
		),
		'text.type.mtime'=> array(
			'code'=>'text.type.mtime',
			'internalcode'=>'mtexty."mtime"',
			'label'=>'Text type modification date/time',
			'type'=> 'datetime',
			'internaltype'=> MW_DB_Statement_Base::PARAM_STR,
		),
		'text.type.editor'=> array(
			'code'=>'text.type.editor',
			'internalcode'=>'mtexty."editor"',
			'label'=>'Text type editor',
			'type'=> 'string',
			'internaltype'=> MW_DB_Statement_Base::PARAM_STR,
		),
	);


	/**
	 * Initializes the object.
	 *
	 * @param MShop_Context_Item_Iface $context Context object
	 */
	public function __construct( MShop_Context_Item_Iface $context )
	{
		parent::__construct( $context );
		$this->setResourceName( 'db-text' );
	}


	/**
	 * Removes old entries from the storage.
	 *
	 * @param array $siteids List of IDs for sites whose entries should be deleted
	 */
	public function cleanup( array $siteids )
	{
		$path = 'classes/text/manager/type/submanagers';
		foreach( $this->getContext()->getConfig()->get( $path, array() ) as $domain ) {
			$this->getSubManager( $domain )->cleanup( $siteids );
		}

		$this->cleanupBase( $siteids, 'mshop/text/manager/type/standard/item/delete' );
	}


	/**
	 * Returns the attributes that can be used for searching.
	 *
	 * @param boolean $withsub Return also attributes of sub-managers if true
	 * @return array List of attribute items implementing MW_Common_Criteria_Attribute_Iface
	 */
	public function getSearchAttributes( $withsub = true )
	{
		/** classes/text/manager/type/submanagers
		 * List of manager names that can be instantiated by the text type manager
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
		$path = 'classes/text/manager/type/submanagers';

		return $this->getSearchAttributesBase( $this->searchConfig, $path, array(), $withsub );
	}


	/**
	 * Returns a new manager for text type extensions.
	 *
	 * @param string $manager Name of the sub manager type in lower case
	 * @param string|null $name Name of the implementation, will be from configuration (or Default) if null
	 * @return MShop_Common_Manager_Iface Manager for different extensions, e.g types, lists etc.
	 */
	public function getSubManager( $manager, $name = null )
	{
		/** classes/text/manager/type/name
		 * Class name of the used text type manager implementation
		 *
		 * Each default text type manager can be replaced by an alternative imlementation.
		 * To use this implementation, you have to set the last part of the class
		 * name as configuration value so the manager factory knows which class it
		 * has to instantiate.
		 *
		 * For example, if the name of the default class is
		 *
		 *  MShop_Text_Manager_Type_Standard
		 *
		 * and you want to replace it with your own version named
		 *
		 *  MShop_Text_Manager_Type_Mytype
		 *
		 * then you have to set the this configuration option:
		 *
		 *  classes/text/manager/type/name = Mytype
		 *
		 * The value is the last part of your own class name and it's case sensitive,
		 * so take care that the configuration value is exactly named like the last
		 * part of the class name.
		 *
		 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
		 * characters are possible! You should always start the last part of the class
		 * name with an upper case character and continue only with lower case characters
		 * or numbers. Avoid chamel case names like "MyType"!
		 *
		 * @param string Last part of the class name
		 * @since 2014.03
		 * @category Developer
		 */

		/** mshop/text/manager/type/decorators/excludes
		 * Excludes decorators added by the "common" option from the text type manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "mshop/common/manager/decorators/default" before they are wrapped
		 * around the text type manager.
		 *
		 *  mshop/text/manager/type/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("MShop_Common_Manager_Decorator_*") added via
		 * "mshop/common/manager/decorators/default" for the text type manager.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/text/manager/type/decorators/global
		 * @see mshop/text/manager/type/decorators/local
		 */

		/** mshop/text/manager/type/decorators/global
		 * Adds a list of globally available decorators only to the text type manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("MShop_Common_Manager_Decorator_*") around the text type manager.
		 *
		 *  mshop/text/manager/type/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "MShop_Common_Manager_Decorator_Decorator1" only to the text controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/text/manager/type/decorators/excludes
		 * @see mshop/text/manager/type/decorators/local
		 */

		/** mshop/text/manager/type/decorators/local
		 * Adds a list of local decorators only to the text type manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("MShop_Common_Manager_Decorator_*") around the text type manager.
		 *
		 *  mshop/text/manager/type/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "MShop_Common_Manager_Decorator_Decorator2" only to the text
		 * controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/text/manager/type/decorators/excludes
		 * @see mshop/text/manager/type/decorators/global
		 */

		return $this->getSubManagerBase( 'text', 'type/' . $manager, $name );
	}


	/**
	 * Returns the config path for retrieving the configuration values.
	 *
	 * @return string Configuration path
	 */
	protected function getConfigPath()
	{
		/** mshop/text/manager/type/standard/item/insert
		 * Inserts a new text type record into the database table
		 *
		 * Items with no ID yet (i.e. the ID is NULL) will be created in
		 * the database and the newly created ID retrieved afterwards
		 * using the "newid" SQL statement.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding
		 * the values from the text type item to the statement before they are
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
		 * @see mshop/text/manager/type/standard/item/update
		 * @see mshop/text/manager/type/standard/item/newid
		 * @see mshop/text/manager/type/standard/item/delete
		 * @see mshop/text/manager/type/standard/item/search
		 * @see mshop/text/manager/type/standard/item/count
		 */

		/** mshop/text/manager/type/standard/item/update
		 * Updates an existing text type record in the database
		 *
		 * Items which already have an ID (i.e. the ID is not NULL) will
		 * be updated in the database.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding
		 * the values from the text type item to the statement before they are
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
		 * @see mshop/text/manager/type/standard/item/insert
		 * @see mshop/text/manager/type/standard/item/newid
		 * @see mshop/text/manager/type/standard/item/delete
		 * @see mshop/text/manager/type/standard/item/search
		 * @see mshop/text/manager/type/standard/item/count
		 */

		/** mshop/text/manager/type/standard/item/newid
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
		 *  SELECT currval('seq_mtexty_id')
		 * For SQL Server:
		 *  SELECT SCOPE_IDENTITY()
		 * For Oracle:
		 *  SELECT "seq_mtexty_id".CURRVAL FROM DUAL
		 *
		 * There's no way to retrive the new ID by a SQL statements that
		 * fits for most database servers as they implement their own
		 * specific way.
		 *
		 * @param string SQL statement for retrieving the last inserted record ID
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/text/manager/type/standard/item/insert
		 * @see mshop/text/manager/type/standard/item/update
		 * @see mshop/text/manager/type/standard/item/delete
		 * @see mshop/text/manager/type/standard/item/search
		 * @see mshop/text/manager/type/standard/item/count
		 */

		/** mshop/text/manager/type/standard/item/delete
		 * Deletes the items matched by the given IDs from the database
		 *
		 * Removes the records specified by the given IDs from the text database.
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
		 * @see mshop/text/manager/type/standard/item/insert
		 * @see mshop/text/manager/type/standard/item/update
		 * @see mshop/text/manager/type/standard/item/newid
		 * @see mshop/text/manager/type/standard/item/search
		 * @see mshop/text/manager/type/standard/item/count
		 */

		/** mshop/text/manager/type/standard/item/search
		 * Retrieves the records matched by the given criteria in the database
		 *
		 * Fetches the records matched by the given criteria from the text
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
		 * @see mshop/text/manager/type/standard/item/insert
		 * @see mshop/text/manager/type/standard/item/update
		 * @see mshop/text/manager/type/standard/item/newid
		 * @see mshop/text/manager/type/standard/item/delete
		 * @see mshop/text/manager/type/standard/item/count
		 */

		/** mshop/text/manager/type/standard/item/count
		 * Counts the number of records matched by the given criteria in the database
		 *
		 * Counts all records matched by the given criteria from the text
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
		 * @see mshop/text/manager/type/standard/item/insert
		 * @see mshop/text/manager/type/standard/item/update
		 * @see mshop/text/manager/type/standard/item/newid
		 * @see mshop/text/manager/type/standard/item/delete
		 * @see mshop/text/manager/type/standard/item/search
		 */

		return 'mshop/text/manager/type/standard/item/';
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