<?php namespace core\field;

use core\IAsArray;
use core\TAsArray;
use core\TEntry;

/**
 * MIT License
 *
 * Copyright (c) 2019 jeamu
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

class Field implements IAsArray
{
	use TAsArray;

	private static $field_count = 0;

	/**
	 * Der Anzeigename
	 * @var $display_name string
	 */
	private $display_name;

	/**
	 * Ob dieses Feld angezeigt werden darf
	 * @var $hidden bool
	 */
	private $hidden;

	/**
	 * Der vorige Name des Feldes
	 * Wird beim Updaten benötigt
	 * @var $previous_name string
	 */
	private $previous_name;

	private $name;
	private $null;

	private $unique;

	public final function __construct()
	{
		$this->display_name = 'field_' . self::$field_count;
		$this->hidden = false;
		$this->name = 'field_' . self::$field_count;
		++self::$field_count;
		$this->null = false;
        $this->previous_name = '';
        $this->unique = false;
	}

    /**
     * Der Anzeigename bei einer generierten Tabelle, der Spaltenname
     * @param string $display_name
     */
    public final function setDisplayName(string $display_name) : void
	{
		$this->display_name = $display_name;
	}

	public final function getDisplayName() : string
	{
		return $this->display_name;
	}

    /**
     * Ob dieses Feld einfach angezeigt werden darf, z. B. in eine RestApi-Schnittstelle verstecken, in eine Tabellenansicht verstecken
     * @param bool $hidden
     */
    public final function setHidden(bool $hidden) : void
	{
		$this->hidden = $hidden;
	}

	public final function getHidden() : bool
	{
		return $this->hidden;
	}

	public final function setName(string $name) : void
	{
		$this->name = $name;
	}

	public final function getName() : string
	{
		return $this->name;
	}

	public final function setNull(bool $null) : void
	{
		$this->null = $null;
	}

	public final function getNull() : bool
	{
		return $this->null;
	}

    /**
     * Setzt den alten Namen dieses Feldes, wird benötigt wenn die Datenbank geupgraded wird um die Felder ordentlich zu wechseln
     * @param string $old_name
     */
    public final function setPreviousName(string $old_name) : void
    {
        $this->previous_name = $old_name;
    }

    public final function getPreviousName() : string
    {
        return $this->previous_name;
    }

    /**
     * Ob der Wert nur einmal in der Datenbank auftauchen darf
     * @param bool $unique
     */
    public final function setUnique(bool $unique) : void
    {
        $this->unique = $unique;
    }

    public final function isUnique() : bool
    {
        return $this->unique;
    }
}