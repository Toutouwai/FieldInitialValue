<?php namespace ProcessWire;

class FieldInitialValue extends WireData implements Module, ConfigurableModule {

	protected $supportedFieldtypes = [
		'FieldtypeText',
		'FieldtypeDatetime',
		'FieldtypeInteger',
		'FieldtypeDecimal',
		'FieldtypeFloat',
		'FieldtypePage',
		'FieldtypeCheckbox',
		'FieldtypeOptions',
		'FieldtypeToggle',
		'FieldtypeSelector',
		'FieldtypeMultiplier',
		'FieldtypeCombo',
		'FieldtypeStars',
	];

	protected $unsupportedFieldtypes = [
		'FieldtypeFile',
		'FieldtypeRepeater',
		'FieldtypePageTable',
		'FieldtypeTable',
	];

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		$this->additionalFieldtypes = '';
	}

	/**
	 * Ready
	 */
	public function ready() {
		$this->addHookAfter('Fieldtype::getConfigInputfields', $this, 'addToConfig');
		$this->addHookAfter('Fieldtype::getConfigAllowContext', $this, 'allowContext');
		$this->pages->addHookAfter('setupNew', $this, 'afterSetupNew', ['priority' => 99]);
	}

	/**
	 * After Fieldtype::getConfigInputfields
	 * Add setting to Field config
	 *
	 * @param HookEvent $event
	 */
	protected function addToConfig(HookEvent $event) {
		/** @var Field $field */
		$field = $event->arguments(0);
		/** @var Fieldtype $fieldtype */
		$fieldtype = $event->object;
		/** @var InputfieldWrapper $wrapper */
		$wrapper = $event->return;

		// Return early if field is an explicitly unsupported fieldtype
		if(wireInstanceOf($field->type, $this->unsupportedFieldtypes)) return;

		// Return early if this is not an enabled fieldtype
		$enabledFieldtypes = $this->supportedFieldtypes;
		if($this->additionalFieldtypes) {
			$lines = explode("\n", str_replace("\r", "", $this->additionalFieldtypes));
			foreach($lines as $line) {
				$line = trim($line);
				if(!$line) continue;
				$enabledFieldtypes[] = $line;
			}
		}
		if(!wireInstanceOf($field->type, $enabledFieldtypes)) return;

		// Return early if fieldtype doesn't match field (applies in ProFields Multiplier)
		if($field->type !== $fieldtype) return;

		// Get the inputfield for this field (using the Home page as the supplied page)
		$dummyPage = $this->wire()->pages->get('/');
		$f = $field->getInputfield($dummyPage);

		// If InputfieldPage get specific Inputfield
		if($f instanceof InputfieldPage) $f = $f->getInputfield();

		$initialValue = $field->fivInitialValue;
		$isEmpty = $this->initialValueEmpty($initialValue);

		// Set inputfield attributes
		$f->name = 'fivInitialValue';
		$f->label = $this->_('Initial value');
		$f->description = $this->_('This will be set as the field value on new pages when they are first created.');
		$f->icon = 'bullseye';
		$f->value = $initialValue;
		if($isEmpty) $f->collapsed = Inputfield::collapsedYes;

		// Radios: add option to clear if field is populated
		if($initialValue && $f instanceof InputfieldRadios) {
			$f->addOption('', $this->_('Clear initial value'));
		}
		// Checkbox: set checked attribute
		elseif($f instanceof InputfieldCheckbox) {
			$f->label2 = $field->label;
			$f->checked = $initialValue ? 'checked' : '';
		}
		// Toggle: allow de-select
		elseif($f instanceof InputfieldToggle) {
			$f->useDeselect = true;
		}
		// Combo
		elseif($f instanceof InputfieldCombo) {
			// Check if field has any unsupported subfields
			$subfields = $f->getComboSettings()->getSubfields();
			$supported = true;
			foreach($subfields as $subfield) {
				if($subfield['type'] === 'File') $supported = false;
				if($subfield['type'] === 'Image') $supported = false;
			}
			if($supported) {
				// Needs special method to set value
				if($initialValue) $f->setValue($initialValue);
			} else {
				// Clear config field and saved value
				$f = null;
				$field->fivInitialValue = null;
				$field->save();
			}
		}

		// Add inputfield to config fields wrapper
		if($f) $wrapper->add($f);
	}

	/**
	 * After Fieldtype::getConfigAllowContext
	 * Allow setting config field in template context
	 *
	 * @param HookEvent $event
	 */
	protected function allowContext(HookEvent $event) {
		$allowed = $event->return;
		$allowed[] = 'fivInitialValue';
		$event->return = $allowed;
	}

	/**
	 * After Pages::setupNew
	 * Set initial field values
	 *
	 * @param HookEvent $event
	 */
	protected function afterSetupNew(HookEvent $event) {
		/** @var Page $page */
		$page = $event->arguments(0);
		foreach($page->getFields() as $field) {
			$initialValue = $field->fivInitialValue;
			if($this->initialValueEmpty($initialValue)) continue;
			$page->set($field->name, $initialValue);
		}
	}

	/**
	 * Is the supplied initial value empty?
	 *
	 * @param mixed $initialValue
	 * @return bool
	 */
	protected function initialValueEmpty($initialValue) {
		return $initialValue === null || $initialValue === '' || (is_array($initialValue) && !$initialValue);
	}

	/**
	 * Config inputfields
	 *
	 * @param InputfieldWrapper $inputfields
	 */
	public function getModuleConfigInputfields($inputfields) {
		$modules = $this->wire()->modules;

		/** @var InputfieldTextarea $f */
		$f = $modules->get('InputfieldTextarea');
		$fName = 'additionalFieldtypes';
		$f->name = $fName;
		$f->label = $this->_('Additional field types');
		$f->description = $this->_('To enable the "initial value" setting for additional field types beyond the default, enter the class names here, one per line. Your mileage may vary.');
		$f->notes = $this->_('The following field types (and those that extend them) are not supportable: FieldtypeFile, FieldtypeRepeater, FieldtypePageTable, FieldtypeTable');
		$f->icon = 'plus-circle';
		$f->value = $this->$fName;
		if(!$f->value) $f->collapsed = Inputfield::collapsedYes;
		$inputfields->add($f);
	}

}
