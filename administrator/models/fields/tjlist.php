<?php
/**
 * @package     Tjfields
 * @subpackage  Com_Tjfields
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @since  1.7.0
 */
class JFormFieldTjList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.5.0
	 */
	protected $type = 'tjlist';

	/**
	 * The class for tjlist input field
	 *
	 * @var    mixed
	 * @since  1.5.0
	 */
	protected $class;

	/**
	 * The other selected value for tjlist input field
	 *
	 * @var    mixed
	 * @since  1.5.0
	 */
	protected $otherSelectedValue;

	/**
	 * The class for tjlist input field for other option
	 *
	 * @var    mixed
	 * @since  1.5.0
	 */
	protected $otherInputClass;

	/**
	 * The required attribute for tjlist input field for other option
	 *
	 * @var    mixed
	 * @since  1.5.0
	 */
	protected $otherInputRequired;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return && $this->element['other'])
		{
			$this->class .= ' tjfieldTjList';
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		// Add Other option language constant for JavaScript
		Text::script('COM_TJFIELDS_TJLIST_OTHER_OPTION');
		Text::script('COM_TJFIELDS_TJLIST_OTHER_OPTION_VALUE');

		$html = parent::getInput();

		$doc = JFactory::getDocument();
		$doc->addScript(JUri::root() . 'administrator/components/com_tjfields/assets/js/tjlist.min.js');

		$options = $this->getOptions();

		if ($this->element['other'])
		{
			// Check if the dropdown is required
			if ($this->element['required'])
			{
				$this->otherInputClass = 'required';

				$this->otherInputRequired = 'required="required"';
			}

			if (!$this->multiple)
			{
				$valueFromSelectList = array_filter(
					$options,
					function ($e) {
						return $e->value == $this->value[0];
					}
				);

				if (empty($valueFromSelectList))
				{
					$this->otherSelectedValue = $this->value[0];
				}
			}
			elseif ($this->multiple)
			{
				// Convert array of objects to array of array
				$dropdownVals = json_encode($options);
				$dropdownVals = json_decode($dropdownVals, true);

				// Get array of dropdown values
				$dropdownVals = array_column($dropdownVals, 'value');

				if (!empty($this->value))
				{
					// Get other/extra values that are not present in dropdown list
					$otherValues = array_values(array_diff($this->value, $dropdownVals));

					if (!empty($otherValues))
					{
						$this->otherSelectedValue = $otherValues[0];
					}
				}
			}

			// If value is other than default list values
			if ($this->otherSelectedValue)
			{
				$html .= $this->getInputBox();

				// Hide dropdown
				$doc->addScriptDeclaration('
					jQuery(document).ready(function() {
						jQuery("select[name=\"' . $this->name . '\"] option[value=\"' . Text::_('COM_TJFIELDS_TJLIST_OTHER_OPTION_VALUE') . '\"]")
						.prop("selected", true).trigger("liszt:updated");
					});
				');
			}
		}

		return $html;
	}

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.9.0
	 */
	public function getOptions()
	{
		$options = array();

		if ($this->element['other'])
		{
			$options[] = HTMLHelper::_('select.option', Text::_('COM_TJFIELDS_TJLIST_OTHER_OPTION_VALUE'), Text::_('COM_TJFIELDS_TJLIST_OTHER_OPTION'));
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the Other option.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.5.0
	 */
	private function getInputBox()
	{
		$text = '<div class="tjfieldTjListOtherText"><br/>';
		$text .= '<input type="text" class=" ' . $this->otherInputClass . '" ' . $this->otherInputRequired . '
					name="' . $this->name . '" id="' . $this->id . '" value="' . $this->otherSelectedValue . '" aria-invalid="false"></div>';

		return $text;
	}
}
