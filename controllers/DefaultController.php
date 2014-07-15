<?php
/**
 * OpenEyes
*
* (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
* (C) OpenEyes Foundation, 2011-2013
* This file is part of OpenEyes.
* OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
* OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
*
* @package OpenEyes
* @link http://www.openeyes.org.uk
* @author OpenEyes <info@openeyes.org.uk>
* @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
* @copyright Copyright (c) 2011-2013, OpenEyes Foundation
* @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
*/

class DefaultController extends OphTrOperationbookingEventController
{
	static protected $action_types = array(
		'cancel' => self::ACTION_TYPE_EDIT,
		'admissionLetter' => self::ACTION_TYPE_PRINT,
		'verifyProcedures' => self::ACTION_TYPE_CREATE,
	);

	public $eventIssueCreate = 'Operation requires scheduling';
	protected $operation_required = false;
	/** @var Element_OphTrOperation_Operation $operation */
	protected $operation = null;

	/**
	 * setup the various js scripts for this controller
	 *
	 * @param CAction $action
	 * @return bool
	 */
	protected function beforeAction($action)
	{
		Yii::app()->clientScript->registerScriptFile($this->assetPath.'/js/booking.js');
		Yii::app()->assetManager->registerScriptFile('js/jquery.validate.min.js');
		Yii::app()->assetManager->registerScriptFile('js/additional-validators.js');
		$this->jsVars['nhs_date_format'] = Helper::NHS_DATE_FORMAT_JS;
		return parent::beforeAction($action);
	}

	/**
	 * @param Element_OphTrOperationbooking_Diagnosis $element
	 * @param $action
	 */
	protected function setElementDefaultOptions_Element_OphTrOperationbooking_Diagnosis($element, $action)
	{
		if ($action == 'create') {
			if ($this->episode && $this->episode->diagnosis) {
				// set default eye and disorder
				$element->eye_id = $this->episode->eye_id;
				$element->disorder_id = $this->episode->disorder_id;
			}
		}
	}

	/**
	 *
	 * @param Element_OphTrOperationbooking_Operation $element
	 * @param $action
	 */
	protected function setElementDefaultOptions_Element_OphTrOperationbooking_Operation($element, $action)
	{
		if ($action == 'create') {
			// set the default eye
			if ($this->episode && $this->episode->diagnosis) {
				$element->eye_id = $this->episode->eye_id;
			}

			// set default anaesthetic based on whether patient is a child or not.
			$key = $this->patient->isChild() ? 'ophtroperationbooking_default_anaesthetic_child' : 'ophtroperationbooking_default_anaesthetic';

			if (isset(Yii::app()->params[$key])) {
				if ($at = AnaestheticType::model()->find('code=?',array(Yii::app()->params[$key]))) {
					$element->anaesthetic_type_id = $at->id;
				}
			}

			if ($default_referral = $this->calculateDefaultReferral()) {
				$element->referral_id = $default_referral->id;
			}

			$element->site_id = Yii::app()->session['selected_site_id'];
		}
	}

	/**
	 * Sets up operation based on the event
	 *
	 * @param $id
	 * @throws CHttpException
	 * (non-phpdoc)
	 * @see BaseEventTypeController::initWithEventId($id)
	 */
	protected function initWithEventId($id)
	{
		parent::initWithEventId($id);

		$this->operation = Element_OphTrOperationbooking_Operation::model()->find('event_id=?',array($this->event->id));
		if ($this->operation_required && !$this->operation) {
			throw new CHttpException(500,'Operation not found');
		}
	}

	/**
	 * Sets up some JS vars for the procedure confirmation checking
	 */
	protected function initActionEdit()
	{
		$this->jsVars['OE_confirmProcedures'] = Yii::app()->params['OphTrOperationbooking_duplicate_proc_warn'];
		$this->jsVars['OE_patientId'] = $this->patient->id;
	}

	/**
	 * Checks whether schedule now has been requested
	 *
	 * (non-phpdoc)
	 * @see BaseEventTypeController::initActionCreate()
	 */
	protected function initActionCreate()
	{
		parent::initActionCreate();
		$this->initActionEdit();
		if (@$_POST['schedule_now']) {
			$this->successUri = 'booking/schedule/';
		}
	}

	/**
	 * Call to edit init
	 *
	 * (non-phpdoc)
	 * @see BaseEventTypeController::initActionUpdate()
	 */
	protected function initActionUpdate()
	{
		parent::initActionUpdate();
		$this->initActionEdit();
	}
	/**
	 * Make the operation element directly available for templates
	 *
	 * @see BaseEventTypeController::initActionView()
	 */
	public function initActionView()
	{
		$this->operation_required = true;
		parent::initActionView();

		$this->extraViewProperties = array(
			'operation' => $this->operation,
		);
	}

	/**
	 * Handle the patient unavailables
	 *
	 * @see BaseEventTypeController::setElementComplexAttributesFromData($element, $data, $index)
	 */
	protected function setComplexAttributes_Element_OphTrOperationbooking_ScheduleOperation($element, $data, $index)
	{
		if (isset($data['Element_OphTrOperationbooking_ScheduleOperation']['patient_unavailables'])) {
			$puns = array();
			foreach($data['Element_OphTrOperationbooking_ScheduleOperation']['patient_unavailables'] as $i => $attributes) {
				if ($id = @$attributes['id']) {
					$pun = OphTrOperationbooking_ScheduleOperation_PatientUnavailable::model()->findByPk($id);
				}
				else {
					$pun = new OphTrOperationbooking_ScheduleOperation_PatientUnavailable();
				}
				$pun->attributes = Helper::convertNHS2MySQL($attributes);
				$puns[] = $pun;
			}
			$element->patient_unavailables = $puns;
		}
	}

	/**
	 * Extend standard behaviour to perform validation of elements across the event
	 *
	 * @param array $data
	 * @return array
	 */
	protected function setAndValidateElementsFromData($data)
	{
		$errors = parent::setAndValidateElementsFromData($data);
		// need to do some validation at the event level

		$event_errors = OphTrOperationbooking_BookingHelper::validateElementsForEvent($this->open_elements);
		if ($event_errors) {
			if (@$errors['Event']) {
				$errors['Event'] = array_merge($errors['Event'], $event_errors);
			}
			else {
				$errors['Event'] = $event_errors;
			}
		}

		return $errors;
	}

	/**
	 * Calculate the default referral for the event
	 *
	 * @return null|Referral
	 */
	public function calculateDefaultReferral()
	{
		$referrals = $this->getReferralChoices();
		$match = null;
		foreach ($referrals as $referral) {
			if ($referral->firm_id == $this->firm->id) {
				return $referral;
			}
			else {
				if (!$match && $referral->service_subspecialty_assignment_id == $this->firm->service_subspecialty_assignment_id) {
					$match = $referral;
				}
			}
		}
		if (!$match && !empty($referrals)) {
			$match = $referrals[0];
		}
		return $match;
	}

	/**
	 * Setup event properties
	 */
	protected function initActionCancel()
	{
		$this->operation_required = true;
		$this->initWithEventId(@$_GET['id']);
	}

	/**
	 * AJAX method to check for any duplicate procedure bookings
	 */
	public function actionVerifyProcedures()
	{
		$this->setPatient($_REQUEST['patient_id']);

		$resp = array(
				'previousProcedures' => false,
		);

		$procs = array();
		$procs_by_id = array();

		if (isset($_POST['Procedures_procs'])) {
			foreach ($_POST['Procedures_procs'] as $proc_id) {
				if ($p = Procedure::model()->findByPk((int)$proc_id)) {
					$procs[] = $p;
					$procs_by_id[$p->id] = $p;
				}
			}
		}

		$eye = Eye::model()->findByPk((int) @$_POST['Element_OphTrOperationbooking_Operation']['eye_id']);

		if ($eye && count($procs)) {
			$matched_procedures = array();
			// get all the operation elements for this patient from booking events that have not been cancelled
			if (Yii::app()->params['OphTrOperationbooking_duplicate_proc_warn_all_eps']) {
				$episodes = $this->patient->episodes;
			}
			else {
				$episodes = array($this->getEpisode());
			}
			foreach ($episodes as $ep)
			{
				$events = $ep->getAllEventsByType($this->event_type->id);
				foreach ($events as $ev) {
					if ($ev->id == @$_POST['event_id']) {
						// if we're editing, then don't want to check against that event
						continue;
					}
					$op = Element_OphTrOperationbooking_Operation::model()->findByAttributes(array('event_id' => $ev->id));

					// check operation still valid, and that it is for a matching eye.
					if (!$op->operation_cancellation_date &&
							($op->eye_id == Eye::BOTH || $eye->id == Eye::BOTH || $op->eye_id == $eye->id)) {

						foreach ($op->procedures as $existing_proc) {
							if (in_array($existing_proc->id, array_keys($procs_by_id))) {
								if (!isset($matched_procedures[$existing_proc->id])) {
									$matched_procedures[$existing_proc->id] = array();
								}
								$matched_procedures[$existing_proc->id][] = $op;
							}
						}
					}
				}
			}

			// if procedure matches
			if (count($matched_procedures)) {
				$resp['previousProcedures'] = true;
				$resp['message'] = $this->renderPartial('previous_procedures', array(
					'matched_procedures' => $matched_procedures,
					'eye' => $eye,
					'procs_by_id' => $procs_by_id
				), true);
			}
		}

		echo \CJSON::encode($resp);
	}

	/**
	 * Cancel operation action
	 *
	 * @param $id
	 * @throws CHttpException
	 * @throws Exception
	 */
	public function actionCancel($id)
	{

		$operation = $this->operation;

		if ($operation->status->name == 'Cancelled') {
			return $this->redirect(array('default/view/'.$this->event->id));
		}

		$errors = array();

		if (isset($_POST['cancellation_reason']) && isset($_POST['operation_id'])) {
			$comment = (isset($_POST['cancellation_comment'])) ? strip_tags(@$_POST['cancellation_comment']) : '';
			$result = $operation->cancel(@$_POST['cancellation_reason'], $comment);

			if ($result['result']) {
				$operation->event->deleteIssues();

				$operation->event->audit('event','cancel');

				die(json_encode(array()));
			}

			foreach ($result['errors'] as $form_errors) {
				foreach ($form_errors as $error) {
					$errors[] = $error;
				}
			}

			die(json_encode($errors));
		}

		if (!$operation = Element_OphTrOperationbooking_Operation::model()->find('event_id=?',array($id))) {
			throw new CHttpException(500,'Operation not found');
		}

		$this->patient = $operation->event->episode->patient;
		$this->title = 'Cancel operation';

		$this->processJsVars();

		$this->render('cancel', array(
			'operation' => $operation,
			'patient' => $operation->event->episode->patient,
			'date' => $operation->minDate,
			'errors' => $errors
		));
	}

	/**
	 * Setup event properties
	 */
	protected function initActionAdmissionLetter()
	{
		$this->operation_required = true;
		$this->initWithEventId(@$_GET['id']);
	}

	/**
	 * Generate admission letter for operation booking
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function actionAdmissionLetter()
	{
		$this->layout = '//layouts/pdf';

		if ($this->patient->date_of_death) {
			// no admission for dead patients
			return false;
		}

		$operation = $this->operation;

		$this->event->audit('admission letter','print',false);

		$this->logActivity('printed admission letter');

		$site = $operation->booking->session->theatre->site;
		if (!$firm = $operation->booking->session->firm) {
			$firm = $operation->event->episode->firm;
			$emergency_list = true;
		}
		$emergency_list = false;

		$pdf_print = new OEPDFPrint('Openeyes', 'Booking letters', 'Booking letters');

		$body = $this->render('../letters/admission_letter', array(
			'site' => $site,
			'patient' => $this->event->episode->patient,
			'firm' => $firm,
			'emergencyList' => $emergency_list,
			'operation' => $operation,
		), true);

		$oeletter = new OELetter(
			$this->event->episode->patient->getLetterAddress(array(
				'include_name' => true,
				'delimiter' => "\n",
			)),
			$site->getLetterAddress(array(
				'include_name' => true,
				'include_telephone' => true,
				'include_fax' => true,
				'delimiter' => "\n",
			))
		);

		$oeletter->setBarcode('E:'.$operation->event_id);
		$oeletter->addBody($body);

		$pdf_print->addLetter($oeletter);

		$body = $this->render('../letters/admission_form', array(
				'operation' => $operation,
				'site' => $site,
				'patient' => $this->event->episode->patient,
				'firm' => $firm,
				'emergencyList' => $emergency_list,
		), true);

		$oeletter = new OELetter;
		$oeletter->setFont('helvetica','10');
		$oeletter->setBarcode('E:'.$operation->event_id);
		$oeletter->addBody($body);

		$pdf_print->addLetter($oeletter);
		$pdf_print->output();
	}
}
