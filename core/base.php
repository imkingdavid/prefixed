<?php

abstract class phpbb_ext_imkingdavid_prefixed_core_base
{
	/**
	 * @var int ID for the prefix
	 */
	protected $id = 0;
	/**
	 * @var dbal Database object instance
	 */
	protected dbal $db = null;

	/**
	 * Constructor method
	 */
	public function __construct(dbal $db, $id = 0)
	{
		$this->set('id', $id);
		$this->set('db', $db);
	}

	/**
	 * Set properties
	 *
	 * @param string $property Which property to modify
	 * @param mixed $value What value to assign to the property
	 * @return null
	 */
	public function set($property, $value)
	{
		// If the property exists let's set it
		if (isset($this->$property))
			$this->$property = $value;
		}
	}

	/**
	 * Get a property's value
	 *
	 * @param string $property The property to get
	 * @return mixed Value of the property, null if !isset($property)
	 */
	public function get($property)
	{
		return (isset($this->$property)) ? $this->property : null;
	}
}
