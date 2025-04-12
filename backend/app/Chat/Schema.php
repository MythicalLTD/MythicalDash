<?php

namespace MythicalDash\Chat;

interface Schema {
	/**
	 * Get the name of the table
	 * 
	 * @return string
	 */
	public static function getTableName(): string;
	/**
	 * The query to create the table
	 * 
	 * @return string
	 */
	public static function createTableQuery() : string;
}