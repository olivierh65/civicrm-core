<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from xml/schema/CRM/Core/CustomGroup.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:b686c4c7a9eb826d2b2aa39841a01d9d)
 */

/**
 * Database access object for the CustomGroup entity.
 */
class CRM_Core_DAO_CustomGroup extends CRM_Core_DAO {
  const EXT = 'civicrm';
  const TABLE_ADDED = '1.1';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_custom_group';

  /**
   * Field to show when displaying a record.
   *
   * @var string
   */
  public static $_labelField = 'title';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Paths for accessing this entity in the UI.
   *
   * @var string[]
   */
  protected static $_paths = [
    'add' => 'civicrm/admin/custom/group/edit?action=add&reset=1',
    'update' => 'civicrm/admin/custom/group/edit?action=update&reset=1&id=[id]',
    'preview' => 'civicrm/admin/custom/group/preview?reset=1&gid=[id]',
    'delete' => 'civicrm/admin/custom/group/delete?reset=1&id=[id]',
  ];

  /**
   * Unique Custom Group ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * Variable name/programmatic handle for this group.
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $name;

  /**
   * Friendly Name.
   *
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $title;

  /**
   * Type of object this group extends (can add other options later e.g. contact_address, etc.).
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $extends;

  /**
   * FK to civicrm_option_value.id (for option group custom_data_type.)
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $extends_entity_column_id;

  /**
   * linking custom group for dynamic object
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $extends_entity_column_value;

  /**
   * Visual relationship between this form and its parent.
   *
   * @var string|null
   *   (SQL type: varchar(15))
   *   Note that values will be retrieved from the database as a string.
   */
  public $style;

  /**
   * Will this group be in collapsed or expanded mode on initial display ?
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $collapse_display;

  /**
   * Description and/or help text to display before fields in form.
   *
   * @var string|null
   *   (SQL type: text)
   *   Note that values will be retrieved from the database as a string.
   */
  public $help_pre;

  /**
   * Description and/or help text to display after fields in form.
   *
   * @var string|null
   *   (SQL type: text)
   *   Note that values will be retrieved from the database as a string.
   */
  public $help_post;

  /**
   * Controls display order when multiple extended property groups are setup for the same class.
   *
   * @var int|string
   *   (SQL type: int)
   *   Note that values will be retrieved from the database as a string.
   */
  public $weight;

  /**
   * Is this property active?
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_active;

  /**
   * Name of the table that holds the values for this group.
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $table_name;

  /**
   * Does this group hold multiple values?
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_multiple;

  /**
   * minimum number of multiple records (typically 0?)
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $min_multiple;

  /**
   * maximum number of multiple records, if 0 - no max
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $max_multiple;

  /**
   * Will this group be in collapsed or expanded mode on advanced search display ?
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $collapse_adv_display;

  /**
   * FK to civicrm_contact, who created this custom group
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $created_id;

  /**
   * Date and time this custom group was created.
   *
   * @var string|null
   *   (SQL type: datetime)
   *   Note that values will be retrieved from the database as a string.
   */
  public $created_date;

  /**
   * Is this a reserved Custom Group?
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_reserved;

  /**
   * Is this property public?
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_public;

  /**
   * crm-i icon class
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $icon;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_custom_group';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? ts('Custom Field Groups') : ts('Custom Field Group');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'created_id', 'civicrm_contact', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Custom Group ID'),
          'description' => ts('Unique Custom Group ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.id',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => '1.1',
        ],
        'name' => [
          'name' => 'name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Custom Group Name'),
          'description' => ts('Variable name/programmatic handle for this group.'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.name',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '1.1',
        ],
        'title' => [
          'name' => 'title',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Custom Group Title'),
          'description' => ts('Friendly Name.'),
          'required' => TRUE,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.title',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 1,
          'html' => [
            'type' => 'Text',
          ],
          'add' => '1.1',
        ],
        'extends' => [
          'name' => 'extends',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Custom Group Extends'),
          'description' => ts('Type of object this group extends (can add other options later e.g. contact_address, etc.).'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.extends',
          'default' => 'Contact',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'CRM_Core_BAO_CustomGroup::getCustomGroupExtendsOptions',
          ],
          'add' => '1.1',
        ],
        'extends_entity_column_id' => [
          'name' => 'extends_entity_column_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Custom Group Subtype List'),
          'description' => ts('FK to civicrm_option_value.id (for option group custom_data_type.)'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.extends_entity_column_id',
          'default' => NULL,
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'html' => [
            'type' => 'ChainSelect',
            'controlField' => 'extends',
          ],
          'pseudoconstant' => [
            'optionGroupName' => 'custom_data_type',
            'optionEditPath' => 'civicrm/admin/options/custom_data_type',
          ],
          'add' => '2.2',
        ],
        'extends_entity_column_value' => [
          'name' => 'extends_entity_column_value',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Custom Group Subtype'),
          'description' => ts('linking custom group for dynamic object'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.extends_entity_column_value',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'serialize' => self::SERIALIZE_SEPARATOR_BOOKEND,
          'html' => [
            'type' => 'ChainSelect',
            'controlField' => 'extends_entity_column_id',
          ],
          'pseudoconstant' => [
            'callback' => 'CRM_Core_BAO_CustomGroup::getExtendsEntityColumnValueOptions',
          ],
          'add' => '1.6',
        ],
        'style' => [
          'name' => 'style',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Custom Group Style'),
          'description' => ts('Visual relationship between this form and its parent.'),
          'maxlength' => 15,
          'size' => CRM_Utils_Type::TWELVE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.style',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'CRM_Core_SelectValues::customGroupStyle',
          ],
          'add' => '1.1',
        ],
        'collapse_display' => [
          'name' => 'collapse_display',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Collapse Custom Group?'),
          'description' => ts('Will this group be in collapsed or expanded mode on initial display ?'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.collapse_display',
          'default' => '0',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '1.1',
        ],
        'help_pre' => [
          'name' => 'help_pre',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => ts('Custom Group Pre Text'),
          'description' => ts('Description and/or help text to display before fields in form.'),
          'rows' => 4,
          'cols' => 80,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.help_pre',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 1,
          'html' => [
            'type' => 'TextArea',
          ],
          'add' => '1.1',
        ],
        'help_post' => [
          'name' => 'help_post',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => ts('Custom Group Post Text'),
          'description' => ts('Description and/or help text to display after fields in form.'),
          'rows' => 4,
          'cols' => 80,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.help_post',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 1,
          'html' => [
            'type' => 'TextArea',
          ],
          'add' => '1.1',
        ],
        'weight' => [
          'name' => 'weight',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Order'),
          'description' => ts('Controls display order when multiple extended property groups are setup for the same class.'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.weight',
          'default' => '1',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '1.1',
        ],
        'is_active' => [
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Custom Group Is Active?'),
          'description' => ts('Is this property active?'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.is_active',
          'default' => '1',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
            'label' => ts("Enabled"),
          ],
          'add' => '1.1',
        ],
        'table_name' => [
          'name' => 'table_name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Table Name'),
          'description' => ts('Name of the table that holds the values for this group.'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.table_name',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'html' => [
            'label' => ts("Table Name"),
          ],
          'readonly' => TRUE,
          'add' => '2.0',
        ],
        'is_multiple' => [
          'name' => 'is_multiple',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Supports Multiple Records'),
          'description' => ts('Does this group hold multiple values?'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.is_multiple',
          'default' => '0',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '2.0',
        ],
        'min_multiple' => [
          'name' => 'min_multiple',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Minimum Multiple Records'),
          'description' => ts('minimum number of multiple records (typically 0?)'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.min_multiple',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '2.2',
        ],
        'max_multiple' => [
          'name' => 'max_multiple',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Maximum Multiple Records'),
          'description' => ts('maximum number of multiple records, if 0 - no max'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.max_multiple',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '2.2',
        ],
        'collapse_adv_display' => [
          'name' => 'collapse_adv_display',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Collapse Group Display'),
          'description' => ts('Will this group be in collapsed or expanded mode on advanced search display ?'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.collapse_adv_display',
          'default' => '0',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '3.0',
        ],
        'created_id' => [
          'name' => 'created_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Created By Contact ID'),
          'description' => ts('FK to civicrm_contact, who created this custom group'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.created_id',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'html' => [
            'label' => ts("Created By"),
          ],
          'add' => '3.0',
        ],
        'created_date' => [
          'name' => 'created_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Custom Group Created Date'),
          'description' => ts('Date and time this custom group was created.'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.created_date',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '3.0',
        ],
        'is_reserved' => [
          'name' => 'is_reserved',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Reserved Group?'),
          'description' => ts('Is this a reserved Custom Group?'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.is_reserved',
          'default' => '0',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '4.4',
        ],
        'is_public' => [
          'name' => 'is_public',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Custom Group Is Public?'),
          'description' => ts('Is this property public?'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.is_public',
          'default' => '1',
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '4.7',
        ],
        'icon' => [
          'name' => 'icon',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Icon'),
          'description' => ts('crm-i icon class'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_custom_group.icon',
          'default' => NULL,
          'table_name' => 'civicrm_custom_group',
          'entity' => 'CustomGroup',
          'bao' => 'CRM_Core_BAO_CustomGroup',
          'localizable' => 0,
          'add' => '5.28',
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return CRM_Core_DAO::getLocaleTableName(self::$_tableName);
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'custom_group', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'custom_group', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [
      'UI_title_extends' => [
        'name' => 'UI_title_extends',
        'field' => [
          0 => 'title',
          1 => 'extends',
        ],
        'localizable' => TRUE,
        'unique' => TRUE,
        'sig' => 'civicrm_custom_group::1::title::extends',
      ],
      'UI_name' => [
        'name' => 'UI_name',
        'field' => [
          0 => 'name',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_custom_group::1::name',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
