<?php

namespace WebChemistry\Images\Addons;

use Kdyby, Nette;

use Nette\Forms\IControl;
use Nette\Forms\Container;

class Replicator extends Kdyby\Replicator\Container {

	/** @var array */
	protected $validation = array(
		'counter' => NULL, 'sizes' => NULL
	);

	/** @var array */
	protected $maxUploads = array(
		'value' => NULL, 'message' => 'Max. count are %arg, you uploaded %count images.'
	);

	/** @var array */
	protected $minUploads = array(
		'value' => NULL, 'message' => 'Min. count are %arg, you uploaded %count images.'
	);

	/** @var array */
	protected $totalSize = array(
		'value' => NULL, 'message' => 'The size of uploaded files can be up to %arg, given %count.'
	);

	/** @var bool|array */
	protected $values = FALSE;

	/************************* Values *************************/

	protected function loadHttpData() {
		parent::loadHttpData();

		$this->getValues();

		if ($this->minUploads['value']) {
			if ($this->validation['counter'] < $this->minUploads['value']) {
				$this->form->addError(str_replace(array('%arg', '%count'), array(
					$this->minUploads['value'], $this->validation['counter']
				), $this->minUploads['message']));
			}
		}

		if ($this->maxUploads['value']) {
			if ($this->validation['counter'] > $this->maxUploads['value']) {
				$this->form->addError(str_replace(array('%arg', '%count'), array(
					$this->maxUploads['value'], $this->validation['counter']
				), $this->maxUploads['message']));
			}
		}

		if ($this->totalSize['value']) {
			if ($this->totalSize['value'] < $this->validation['sizes']) {
				$this->form->addError(str_replace(array(
					'%arg', '%count'
				), array(
					$this->formatBytes($this->totalSize['value'], 0), $this->formatBytes($this->validation['sizes'], 0)
				), $this->totalSize['message']));
			}
		}
	}

	public function setDefaults($values, $erase = FALSE) {
		$form = $this->getForm(FALSE);

		if (!$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValues((array) $values, $erase);
		}

		return $this;
	}

	/************************* Validators *************************/

	public function setMinUploads($minUploads, $message = NULL) {
		$this->minUploads['value'] = $minUploads;

		if ($message) {
			$this->minUploads['message'] = $message;
		}

		return $this;
	}

	public function setMaxUploads($maxUploads, $message = NULL) {
		$this->maxUploads['value'] = $maxUploads;

		if ($message) {
			$this->maxUploads['message'] = $message;
		}

		return $this;
	}

	public function setMaxTotalSize($maxSize, $message = NULL) {
		$this->totalSize['value'] = $maxSize;

		if ($message) {
			$this->totalSize['message'] = $message;
		}

		return $this;
	}

	protected function createDefault() {
		if (!$this->createDefault) {
			return;
		}

		if (!$this->getForm()
				  ->isSubmitted()
		) {
			foreach (range(count($this->getComponents()) - 1, count($this->getComponents()) + $this->createDefault - 2) as $key) {
				$control = $this->createOne($key);
			}
		} elseif ($this->forceDefault) {
			while (iterator_count($this->getContainers()) < $this->createDefault) {
				$this->createOne();
			}
		}
	}

	public function setValues($values, $erase = FALSE, $onlyDisabled = FALSE) {
		if ($values instanceof \Traversable) {
			$values = iterator_to_array($values);
		} else {
			if (!is_array($values)) {
				throw new Nette\InvalidArgumentException(sprintf('First parameter must be an array, %s given.', gettype($values)));
			}
		}

		if (!$this->form->isAnchored() || !$this->form->isSubmitted()) {
			foreach ($values as $name => $value) {
				if ($value) {
					$control = $this->createOne($name);
				}
			}
		}

		foreach ($this->getComponents() as $name => $control) {
			if ($control instanceof IControl) {
				if (array_key_exists($name, $values)) {
					$control->setValue($values[$name]);
				} elseif ($erase) {
					$control->setValue(NULL);
				}
			} elseif ($control instanceof Container) {
				if (array_key_exists($name, $values)) {
					unset($control[MultiUploadControl::DELETE_NAME], $control[MultiUploadControl::ADD_NAME]);
					$control->setValues(array('upload' => $values[$name]), $erase);
					$control[MultiUploadControl::UPLOAD_NAME]->hasActions = FALSE;
				}
			}
		}

		return $this;
	}

	/**
	 * Returns the values submitted by the form.
	 *
	 * @param  bool  return values as an array?
	 * @return Nette\Utils\ArrayHash|array
	 */
	public function getValues($asArray = FALSE) {
		if ($this->values !== FALSE) {
			return $this->values;
		}

		$values = array();
		$totalSize = 0;

		foreach ($this->getComponents(FALSE, 'Nette\Forms\Container') as $name => $control) {
			$upload = $control[MultiUploadControl::UPLOAD_NAME];
			$upload->loadHttpData();

			if ($upload->getValue()) {
				if (is_array($upload->getValue())) {
					$values = array_merge($values, $upload->getValue());
				} else {
					$values[] = $upload->getValue();
				}
			}
		}

		$this->validation['sizes'] = $totalSize;
		$this->validation['counter'] = count($values);

		return $this->values = $values;
	}

	/************************* Other *************************/

	/**
	 * @return array
	 */
	protected function uploadArray($value) {
		return $value instanceof Nette\Http\FileUpload ? array($value) : (array) $value;
	}

	protected function formatBytes($size, $precision = 2) {
		$base = log($size, 1024);
		$suffixes = array('B', 'kB', 'MB', 'GB', 'TB');

		return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
	}
}
