<?php /**
	 * OpenEyes
	 *
	 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
	 * (C) OpenEyes Foundation, 2011-2012
	 * This file is part of OpenEyes.
	 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
	 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @package OpenEyes
	 * @link http://www.openeyes.org.uk
	 * @author OpenEyes <info@openeyes.org.uk>
	 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
	 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
	 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
	 */

	/**
	 * This is the model class for table "et_ophtroperation_operation".
	 *
	 * The followings are the available columns in table:
	 * @property string $id
	 * @property integer $event_id
	 * @property integer $eye_id
	 * @property integer $consultant_required
	 * @property integer $anaesthetic_type_id
	 * @property integer $overnight_stay
	 * @property integer $site_id
	 * @property integer $priority_id
	 * @property string $decision_date
	 * @property string $comments
	 *
	 * The followings are the available model relations:
	 *
	 * @property ElementType $element_type
	 * @property EventType $eventType
	 * @property Event $event
	 * @property User $user
	 * @property User $usermodified
	 * @property Eye $eye
	 * @property OphTrOperation_Operation_Procedures $procedures
	 * @property AnaestheticType $anaesthetic_type
	 * @property Site $site
	 * @property Element_OphTrOperation_Operation_Priority $priority
	 */

	class Element_OphTrOperation_Operation extends BaseEventTypeElement
	{
		const LETTER_INVITE = 0;
		const LETTER_REMINDER_1 = 1;
		const LETTER_REMINDER_2 = 2;
		const LETTER_GP = 3;
		const LETTER_REMOVAL = 4;

		// these reflect an actual status, relating to actions required rather than letters sent
		const STATUS_WHITE = 0; // no action required.	the default status.
		const STATUS_PURPLE = 1; // no invitation letter has been sent
		const STATUS_GREEN1 = 2; // it's two weeks since an invitation letter was sent with no further letters going out
		const STATUS_GREEN2 = 3; // it's two weeks since 1st reminder was sent with no further letters going out
		const STATUS_ORANGE = 4; // it's two weeks since 2nd reminder was sent with no further letters going out
		const STATUS_RED = 5; // it's one week since gp letter was sent and they're still on the list
		const STATUS_NOTWAITING = null;

		public $service;

		/**
		 * Returns the static model of the specified AR class.
		 * @return the static model class
		 */
		public static function model($className = __CLASS__)
		{
			return parent::model($className);
		}

		/**
		 * @return string the associated database table name
		 */
		public function tableName()
		{
			return 'et_ophtroperation_operation';
		}

		/**
		 * @return array validation rules for model attributes.
		 */
		public function rules()
		{
			// NOTE: you should only define rules for those attributes that
			// will receive user inputs.
			return array(
				array('event_id, eye_id, consultant_required, anaesthetic_type_id, overnight_stay, site_id, priority_id, decision_date, comments, anaesthetist_required, total_duration, status_id, cancellation_date, cancellation_reason_id, cancellation_comment, cancellation_user_id', 'safe'),
				array('eye_id, consultant_required, anaesthetic_type_id, overnight_stay, site_id, priority_id, decision_date', 'required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, event_id, eye_id, consultant_required, anaesthetic_type_id, overnight_stay, site_id, priority_id, decision_date, comments, ', 'safe', 'on' => 'search'),
		);
	}
	
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'element_type' => array(self::HAS_ONE, 'ElementType', 'id','on' => "element_type.class_name='".get_class($this)."'"),
			'eventType' => array(self::BELONGS_TO, 'EventType', 'event_type_id'),
			'event' => array(self::BELONGS_TO, 'Event', 'event_id'),
			'user' => array(self::BELONGS_TO, 'User', 'created_user_id'),
			'usermodified' => array(self::BELONGS_TO, 'User', 'last_modified_user_id'),
			'eye' => array(self::BELONGS_TO, 'Eye', 'eye_id'),
			'procedures' => array(self::HAS_MANY, 'OphTrOperation_Operation_Procedures', 'element_id'),
			'anaesthetic_type' => array(self::BELONGS_TO, 'AnaestheticType', 'anaesthetic_type_id'),
			'site' => array(self::BELONGS_TO, 'Site', 'site_id'),
			'priority' => array(self::BELONGS_TO, 'OphTrOperation_Operation_Priority', 'priority_id'),
			'status' => array(self::BELONGS_TO, 'OphTrOperation_Operation_Status', 'status_id'),
			'erod' => array(self::HAS_ONE, 'OphTrOperation_Operation_EROD', 'element_id'),
			'date_letter_sent' => array(self::HAS_ONE, 'OphTrOperation_Operation_Date_Letter_Sent', 'element_id', 'order' => 'date_letter_sent.id DESC'),
			'cancellation_user' => array(self::BELONGS_TO, 'User', 'cancellation_user_id'),
			'cancellation_reason' => array(self::BELONGS_TO, 'OphTrOperation_Operation_Cancellation_Reason', 'cancellation_reason_id'),
			'cancelledBookings' => array(self::HAS_MANY, 'OphTrOperation_Operation_Booking', 'element_id', 'condition' => 'cancellation_date is not null', 'order' => 'cancellation_date'),
			'booking' => array(self::HAS_ONE, 'OphTrOperation_Operation_Booking', 'element_id', 'condition' => 'cancellation_date is null'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'event_id' => 'Event',
			'eye_id' => 'Eyes',
			'procedures' => 'Operations',
			'consultant_required' => 'Consultant required',
			'anaesthetic_type_id' => 'Anaesthetic type',
			'overnight_stay' => 'Post operative stay',
			'site_id' => 'Site',
			'priority_id' => 'Priority',
			'decision_date' => 'Decision date',
			'comments' => 'Add comments',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('event_id', $this->event_id, true);

		$criteria->compare('eye_id', $this->eye_id);
		$criteria->compare('procedures', $this->procedures);
		$criteria->compare('consultant_required', $this->consultant_required);
		$criteria->compare('anaesthetic_type_id', $this->anaesthetic_type_id);
		$criteria->compare('overnight_stay', $this->overnight_stay);
		$criteria->compare('site_id', $this->site_id);
		$criteria->compare('priority_id', $this->priority_id);
		$criteria->compare('decision_date', $this->decision_date);
		$criteria->compare('comments', $this->comments);
		
		return new CActiveDataProvider(get_class($this), array(
				'criteria' => $criteria,
			));
	}

	/**
	 * Set default values for forms on create
	 */
	public function setDefaultOptions() {
		$patient_id = (int) $_REQUEST['patient_id'];
		$firm = Yii::app()->getController()->firm;
		$episode = Episode::getCurrentEpisodeByFirm($patient_id, $firm);
		if($episode && $episode->diagnosis) {
			$this->eye_id = $episode->eye_id;
		}
	}

	public function getproc_defaults() {
		$ids = array();
		foreach (OphTrOperation_Operation_Defaults::model()->findAll() as $item) {
			$ids[] = $item->value_id;
		}
		return $ids;
	}

	protected function beforeSave()
	{
		$anaesthetistRequired = array(
			'LAC','LAS','GA'
		);
		$this->anaesthetist_required = in_array($this->anaesthetic_type->name, $anaesthetistRequired);

		if (!$this->status_id) {
			$this->status_id = 1;
		}

		return parent::beforeSave();
	}

	protected function afterSave()
	{
		if (!empty($_POST['Procedures'])) {

			$existing_ids = array();

			foreach (OphTrOperation_Operation_Procedures::model()->findAll('element_id = :elementId', array(':elementId' => $this->id)) as $item) {
				$existing_ids[] = $item->proc_id;
			}

			foreach ($_POST['Procedures'] as $id) {
				if (!in_array($id,$existing_ids)) {
					$item = new OphTrOperation_Operation_Procedures;
					$item->element_id = $this->id;
					$item->proc_id = $id;

					if (!$item->save()) {
						throw new Exception('Unable to save MultiSelect item: '.print_r($item->getErrors(),true));
					}
				}
			}

			foreach ($existing_ids as $id) {
				if (!in_array($id,$_POST['Procedures'])) {
					$item = OphTrOperation_Operation_Procedures::model()->find('element_id = :elementId and proc_id = :lookupfieldId',array(':elementId' => $this->id, ':lookupfieldId' => $id));
					if (!$item->delete()) {
						throw new Exception('Unable to delete MultiSelect item: '.print_r($item->getErrors(),true));
					}
				}
			}
		}

		return parent::afterSave();
	}

	protected function beforeValidate()
	{
		return parent::beforeValidate();
	}

	protected function afterValidate() {
		if (!empty($_POST['Element_OphTrOperation_Operation']) && empty($_POST['Procedures'])) {
			$this->addError('procedures', 'At least one procedure must be entered');
		}

		return parent::afterValidate();
	}

	public static function getLetterOptions()
	{
		return array(
			'' => 'Any',
			self::LETTER_INVITE => 'Invitation',
			self::LETTER_REMINDER_1 => '1st Reminder',
			self::LETTER_REMINDER_2 => '2nd Reminder',
			self::LETTER_GP => 'Refer to GP'
		);
	}

	public function getLetterType() {
		$letterTypes = $this->getLetterOptions();
		$letterType = ($this->getDueLetter() !== null && isset($letterTypes[$this->getDueLetter()])) ? $letterTypes[$this->getDueLetter()] : false;

		if ($letterType == false && $this->getLastLetter() == self::LETTER_GP) {
			$letterType = 'Refer to GP';
		}

		return $letterType;
	}

	public function getHas_gp() {
		return ($this->getDueLetter() != self::LETTER_GP || ($this->event->episode->patient->practice && $this->event->episode->patient->practice->address));
	}

	public function getHas_address() {
		return (bool)$this->event->episode->patient->correspondAddress;
	}

	public function getLastLetter()
	{
		if (!$this->date_letter_sent) {
			return null;
		}
		if (
			!is_null($this->date_letter_sent->date_invitation_letter_sent) and
			$this->date_letter_sent->date_invitation_letter_sent and	// an invitation letter has been sent
			is_null($this->date_letter_sent->date_1st_reminder_letter_sent) and // but no 1st reminder
			is_null($this->date_letter_sent->date_2nd_reminder_letter_sent) and // no 2nd reminder
			is_null($this->date_letter_sent->date_gp_letter_sent) // no gp letter
		) {
			return self::LETTER_INVITE;
		}
		if (
			$this->date_letter_sent->date_invitation_letter_sent and	// an invitation letter has been sent
			$this->date_letter_sent->date_1st_reminder_letter_sent and // and a 1st reminder
			is_null($this->date_letter_sent->date_2nd_reminder_letter_sent) and // but no 2nd reminder
			is_null($this->date_letter_sent->date_gp_letter_sent) // no gp letter
		) {
			return self::LETTER_REMINDER_1;
		}
		if (
			$this->date_letter_sent->date_invitation_letter_sent and	// an invitation letter has been sent
			$this->date_letter_sent->date_1st_reminder_letter_sent and // and a 1st reminder
			$this->date_letter_sent->date_2nd_reminder_letter_sent and // and a 2nd reminder
			is_null($this->date_letter_sent->date_gp_letter_sent) // no gp letter
		) {
			return self::LETTER_REMINDER_2;
		}
		if (
			$this->date_letter_sent->date_invitation_letter_sent and	// an invitation letter has been sent
			$this->date_letter_sent->date_1st_reminder_letter_sent and // and a 1st reminder
			$this->date_letter_sent->date_2nd_reminder_letter_sent and // and a 2nd reminder
			$this->date_letter_sent->date_gp_letter_sent // and a gp letter
		) {
			return self::LETTER_GP;
		}
		return null;
	}

	public function getNextLetter()
	{
		if (is_null($this->getLastLetter())) {
			return self::LETTER_INVITE;
		} else {
			$lastletter = $this->getLastLetter();
			if ($lastletter == self::LETTER_INVITE) {
				return self::LETTER_REMINDER_1;
			} elseif ($lastletter == self::LETTER_REMINDER_1) {
				return self::LETTER_REMINDER_2;
			} elseif ($lastletter == self::LETTER_REMINDER_2) {
				return self::LETTER_GP;
			} elseif ($lastletter == self::LETTER_GP) {
				return self::LETTER_REMOVAL;
			}
		}
	}

	public function getDueLetter()
	{
		$lastletter = $this->getLastLetter();
		if (!$this->getWaitingListStatus()) { // if getwaitingliststatus returns null, we're white
			return $lastletter; // no new letter is due, so we should print the last one
		}
		if ($this->getWaitingListStatus() == self::STATUS_PURPLE) {
			return self::LETTER_INVITE;
		} elseif ($this->getWaitingListStatus() == self::STATUS_GREEN1) {
			return self::LETTER_REMINDER_1;
		} elseif ($this->getWaitingListStatus() == self::STATUS_GREEN2) {
			return self::LETTER_REMINDER_2;
		} elseif ($this->getWaitingListStatus() == self::STATUS_ORANGE) {
			return self::LETTER_GP;
		} elseif ($this->getWaitingListStatus() == self::STATUS_RED) {
			return null; // possibly this should return the gp letter, though it's already been sent?
		} else {
			return null; // possibly this should return $lastletter ?
		}
	}

	/**
	 * Returns the letter status for an operation.
	 *
	 * Checks to see if it's an operation to be scheduled or an operation to be rescheduled. If it's the former it bases its calculation
	 *	 on the operation creation date. If it's the latter it bases it on the most recent cancelled_booking creation date.
		 *
	 * return int
	 */
	public function getWaitingListStatus()
	{
		if (is_null($this->getLastLetter())) {
			return self::STATUS_PURPLE; // no invitation letter has been sent
		} elseif (
			is_null($this->date_letter_sent->date_invitation_letter_sent) and
			is_null($this->date_letter_sent->date_1st_reminder_letter_sent) and
			is_null($this->date_letter_sent->date_2nd_reminder_letter_sent) and
			is_null($this->date_letter_sent->date_gp_letter_sent)
		) {
			return self::STATUS_PURPLE; // no invitation letter has been sent
		}

		$now = new DateTime(); $now->setTime(0,0,0); // $two_weeks_ago = $now->modify('-14 days');
		$now = new DateTime(); $now->setTime(0,0,0); // $one_week_ago = $now->modify('-7 days');

		// if the last letter was the invitation and it was sent over two weeks ago from now:
		$date_sent = new DateTime($this->date_letter_sent->date_invitation_letter_sent); $date_sent->setTime(0,0,0);
		if ( ($this->getLastLetter() == self::LETTER_INVITE) and ($now->getTimestamp() - $date_sent->getTimestamp() > 1209600) ) {
			return self::STATUS_GREEN1;
		}

		// if the last letter was the 1st reminder and it was sent over two weeks ago from now:
		$date_sent = new DateTime($this->date_letter_sent->date_1st_reminder_letter_sent); $date_sent->setTime(0,0,0);
		if ( ($this->getLastLetter() == self::LETTER_REMINDER_1) and ($now->getTimestamp() - $date_sent->getTimestamp() > 1209600) ) {
			return self::STATUS_GREEN2;
		}

		// if the last letter was the 2nd reminder and it was sent over two weeks ago from now:
		$date_sent = new DateTime($this->date_letter_sent->date_2nd_reminder_letter_sent); $date_sent->setTime(0,0,0);
		if ( ($this->getLastLetter() == self::LETTER_REMINDER_2) and ($now->getTimestamp() - $date_sent->getTimestamp() > 1209600) ) {
			return self::STATUS_ORANGE;
		}
		// if the last letter was the gp letter and it was sent over one week ago from now:
		$date_sent = new DateTime($this->date_letter_sent->date_gp_letter_sent); $date_sent->setTime(0,0,0);
		if ( ($this->getLastLetter() == self::LETTER_GP) and ($now->getTimestamp() - $date_sent->getTimestamp() > 604800) ) {
			return self::STATUS_RED;
		}
		return null;
	}

	public function getWaitingListLetterStatus()
	{
		echo var_export($this->date_letter_sent,true);
		Yii::app()->end();
	}

	public function getMinDate() {
		$date = strtotime($this->event->datetime);

		if ($this->schedule_timeframe->schedule_options_id != 1) {
			$interval = str_replace('After ', '+', $this->getScheduleText());
			$date = strtotime($interval, $date);
		}

		$thisMonth = mktime(0, 0, 0, date('m'), 1, date('Y'));

		if ($date < $thisMonth) {
			return $thisMonth;
		}

		return $date;
	}

	public function getSchedule_timeframe() {
		return Element_OphTrOperation_ScheduleOperation::model()->find('event_id=?',array($this->event_id));
	}

	public function getFirmCalendarForMonth($firm, $timestamp) {
		$sessions = array();

		$year = date('Y',$timestamp);
		$month = date('m',$timestamp);

		$rttDate = date('Y-m-d',strtotime('+6 weeks', strtotime($this->decision_date)));

		$criteria = new CDbCriteria;
		$criteria->compare("firm_id",$firm->id);
		$criteria->compare('available',1);
		$criteria->addSearchCondition("date","$year-$month-%",false);
		$criteria->order = "date asc";

		$days = array();
		$sessiondata = array();

		foreach (OphTrOperation_Operation_Session::model()->findAll($criteria) as $session) {
			$day = date('D',strtotime($session->date));

			$sessiondata[$session->date][] = $session;
			$days[$day][] = $session->date;
		}

		$sessions = array();

		foreach ($days as $day => $dates) {
			for ($i=1;$i<=date('t',mktime(0,0,0,$month,1,$year));$i++) {
				if (date('D',mktime(0,0,0,$month,$i,$year)) == $day) {
					$date = "$year-$month-".str_pad($i,2,'0',STR_PAD_LEFT);
					if (in_array($date,$dates)) {
						$open = $full = 0;

						if (strtotime($date) < strtotime(date('Y-m-d'))) {
							$status = 'inthepast';
						} else {
							foreach ($sessiondata[$date] as $session) {
								if ($session->availableMinutes >= $this->total_duration) {
									$open++;
								} else {
									$full++;
								}
							}

							if ($full == count($sessiondata[$date])) {
								$status = 'full';
							} else if ($full >0 and $open >0) {
								$status = 'limited';
							} else if ($open == count($sessiondata[$date])) {
								$status = 'available';
							}
						}

						if ($date >= $rttDate) {
							$status .= ' outside_rtt';
						}
					} else {
						$status = 'closed';
					}

					$sessions[$day][$date] = array(
						'status' => $status,
					);
				}
			}
		}

		return $this->fixCalendarDateOrdering($sessions);
	}

	public function fixCalendarDateOrdering($sessions) {
		$return = array();

		foreach (array('Mon','Tue','Wed','Thu','Fri','Sat','Sun') as $day) {
			if (isset($sessions[$day])) {
				$return[$day] = $sessions[$day];
			}
		}

		$max = 0;

		$datelist = array();

		$dayn = 0;
		$day_lookup = array();
		$session_lookup = array();

		foreach ($return as $day => $dates) {
			foreach ($dates as $date => $session) {
				$datelist[$dayn][] = $date;
				$session_lookup[$date] = $session;
			}
			$day_lookup[$dayn] = $day;
			$dayn++;
		}

		while (1) {
			$changed = false;
			$datelist2 = array();

			foreach ($datelist as $day => $dates) {
				foreach ($dates as $i => $date) {
					if (isset($datelist[$day+1][$i]) && $date > $datelist[$day+1][$i]) {
						if (!isset($datelist2[$day]) || !in_array(date('Y-m-d',strtotime($date)-(86400*7)),$datelist2[$day])) {
							$datelist2[$day][] = date('Y-m-d',strtotime($date)-(86400*7));
							$session_lookup[date('Y-m-d',strtotime($date)-(86400*7))] = array('status' => 'blank');
							$changed = true;
						}
					}
					if (!isset($datelist2[$day]) || !in_array($date,$datelist2[$day])) {
						$datelist2[$day][] = $date;
					}
				}
			}

			if (!$changed) break;
			$datelist = $datelist2;
		}

		$sessions = array();

		foreach ($datelist2 as $dayn => $dates) {
			foreach ($dates as $date) {
				$sessions[$day_lookup[$dayn]][$date] = $session_lookup[$date];
			}
		}

		return $sessions;
	}

	public function getTheatres($date, $emergency = false)
	{
		if (empty($date)) {
			throw new Exception('Date is required.');
		}

		if (empty($emergency) || $emergency == 'EMG') {
			$firmId = null;
		} else {
			$firmId = $emergency;
		}

		$sessions = OphTrOperation_Operation_Theatre::findByDateAndFirmID($date, $firmId);

		$results = array();
		$names = array();
		foreach ($sessions as $session) {
			$theatre = OphTrOperation_Operation_Theatre::model()->findByPk($session['id']);

			$name = $session['name'] . ' (' . $theatre->site->short_name . ')';
			$sessionTime = explode(':', $session['session_duration']);
			$session['duration'] = ($sessionTime[0] * 60) + $sessionTime[1];
			$session['time_available'] = $session['duration'] - $session['bookings_duration'];
			$session['id'] = $session['session_id'];
			unset($session['session_duration'], $session['date'], $session['name']);

			// Add status field to indicate if session is full or not
			if ($session['time_available'] <= 0) {
				$session['status'] = 'full';
			} else {
				$session['status'] = 'available';
			}

			$session['date'] = $date;

			// Add bookable field to indicate if session can be booked for this operation
			$bookable = true;
			if($this->anaesthetist_required && !$session['anaesthetist']) {
				$bookable = false;
				$session['bookable_reason'] = 'anaesthetist';
			}
			if($this->consultant_required && !$session['consultant']) {
				$bookable = false;
				$session['bookable_reason'] = 'consultant';
			}
			$paediatric = ($this->event->episode->patient->isChild());
			if($paediatric && !$session['paediatric']) {
				$bookable = false;
				$session['bookable_reason'] = 'paediatric';
			}
			if($this->anaesthetic_type->name == 'GA' && !$session['general_anaesthetic']) {
				$bookable = false;
				$session['bookable_reason'] = 'general_anaesthetic';
			}
			if ($session['date'] < date('Y-m-d')) {
				$bookable = false;
				$session['bookable_reason'] = 'inthepast';
			}
			$session['bookable'] = $bookable;
			$results[$name][] = $session;
			if (!in_array($name, $names)) {
				$names[] = $name;
			}

		}

		if (count($results) > 1) {
			array_multisort($names, SORT_ASC, $results);
		}

		return $results;
	}

	public function getWardOptions($siteId, $theatreId = null) {
		if (!$site = Site::model()->findByPk($siteId)) {
			throw new Exception('Invalid site id');
		}

		$results = array();

		if (!empty($theatreId)) {
			if ($ward = OphTrOperation_Operation_Ward::model()->find('theatre_id=?',array($theatreId))) {
				$results[$ward->id] = $ward->name;
			}
		}

		if (empty($results)) {
			// otherwise select by site and patient age/gender
			$patient = $this->event->episode->patient;

			$genderRestrict = $ageRestrict = 0;
			$genderRestrict = ('M' == $patient->gender) ? OphTrOperation_Operation_Ward::RESTRICTION_MALE : OphTrOperation_Operation_Ward::RESTRICTION_FEMALE;
			$ageRestrict = ($patient->isChild()) ? OphTrOperation_Operation_Ward::RESTRICTION_CHILD : OphTrOperation_Operation_Ward::RESTRICTION_ADULT;

			$whereSql = 's.id = :id AND
				(w.restriction & :r1 > 0) AND (w.restriction & :r2 > 0)';
			$whereParams = array(
				':id' => $siteId,
				':r1' => $genderRestrict,
				':r2' => $ageRestrict
			);

			$wards = Yii::app()->db->createCommand()
				->select('w.id, w.name')
				->from('ophtroperation_operation_ward w')
				->join('site s', 's.id = w.site_id')
				->where($whereSql, $whereParams)
				->queryAll();

			$results = array();

			foreach ($wards as $ward) {
				$results[$ward['id']] = $ward['name'];
			}
		}

		return $results;
	}

	public function calculateEROD($booking_session_id) {
		$where = '';

		if ($this->cancelledBookings) {
			OELog::log("We have cancelled bookings so we dont set EROD");
			return false;
		} else {
			OELog::log("No cancelled bookings so we set EROD");
		}
		$service_subspecialty_assignment_id = $this->event->episode->firm->service_subspecialty_assignment_id;

		if ($this->consultant_required) {
			$where .= " and session.consultant = 1";
		}

		if ($this->event->episode->patient->isChild()) {
			$where .= " and session.paediatric = 1";

			$service_subspecialty_assignment_id = $this->event->element_operation->booking->session->firm->serviceSubspecialtyAssignment->id;
		}

		if ($this->anaesthetist_required || $this->anaesthetic_type->code == 'GA') {
			$where .= " and session.anaesthetist = 1 and session.general_anaesthetic = 1";
		}

		$lead_time_date = date('Y-m-d',strtotime($this->decision_date) + (86400 * 7 * Yii::app()->params['erod_lead_time_weeks']));

		if ($rule = OphTrOperation_Operation_EROD_Rule::model()->find('subspecialty_id=?',array($this->event->episode->firm->serviceSubspecialtyAssignment->subspecialty_id))) {
			$firm_ids = array();
			foreach ($rule->items as $item) {
				if ($item->item_type == 'firm') {
					$firm_ids[] = $item->item_id;
				}
			}

			$where .= " and firm.id in (".implode(',',$firm_ids).")";
		} else {
			$where .= " and firm.service_subspecialty_assignment_id = $service_subspecialty_assignment_id";
		}

		foreach ($erod = Yii::app()->db->createCommand()->select("ophtroperation_operation_session.id as session_id, date, start_time, end_time, firm.name as firm_name, firm.id as firm_id, subspecialty.name as subspecialty_name, consultant, paediatric, anaesthetist, general_anaesthetic")
			->from("ophtroperation_operation_session")
			->join("firm","firm.id = ophtroperation_operation_session.firm_id")
			->join("ophtroperation_operation_booking","ophtroperation_operation_booking.session_id = ophtroperation_operation_session.id")
			->join("et_ophtroperation_operation","ophtroperation_operation_booking.element_id = et_ophtroperation_operation.id")
			->join("service_subspecialty_assignment ssa","ssa.id = firm.service_subspecialty_assignment_id")
			->join("subspecialty","subspecialty.id = ssa.subspecialty_id")
			->join("ophtroperation_operation_theatre","ophtroperation_operation_session.theatre_id = ophtroperation_operation_theatre.id")
			->where("ophtroperation_operation_session.date > '$lead_time_date' and ophtroperation_operation_session.available = 1 $where")
			->group("ophtroperation_operation_session.id")
			->order("ophtroperation_operation_session.date, ophtroperation_operation_session.start_time")
			->queryAll() as $row) {
			// removed this from the theatre join: and theatre.id != 10")		~chrisr

			$session = OphTrOperation_Operation_Session::model()->findByPk($row['session_id']);
			// if the session has no firm, under the existing booking logic it is an emergency session
			if (!$session->firm) {
				continue;
			}
			$available_time = $session->availableMinutes;

			if ($session->id == $booking_session_id) {
				// this is so that the available_time value saved below is accurate
				$available_time -= $this->total_duration;
			}

			if ($available_time >= $this->total_duration) {
				$erod = new OphTrOperation_Operation_EROD;
				$erod->element_id = $this->id;
				$erod->session_id = $row['session_id'];
				$erod->session_date = $row['date'];
				$erod->session_start_time = $row['start_time'];
				$erod->session_end_time = $row['end_time'];
				$erod->firm_id = $row['firm_id'];
				$erod->consultant = $row['consultant'];
				$erod->paediatric = $row['paediatric'];
				$erod->anaesthetist = $row['anaesthetist'];
				$erod->general_anaesthetic = $row['general_anaesthetic'];
				$erod->session_duration = $session->duration;
				$erod->total_operations_time = $session->bookedMinutes;
				$erod->available_time = $available_time;

				if (!$erod->save()) {
					throw new Exception('Unable to save EROD: '.print_r($erod->getErrors(),true));
				}

				break;
			}
		}
	}

	public function audit($target, $action, $data=null, $log=false, $properties=array()) {
		$properties['event_id'] = $this->event_id;
		$properties['episode_id'] = $this->event->episode_id;
		$properties['patient_id'] = $this->event->episode->patient_id;

		return parent::audit($target, $action, $data, $log, $properties);
	}

	public function cancel($reason_id, $comment = null) {
		if (!$reason = OphTrOperation_Operation_Cancellation_Reason::model()->findByPk($reason_id)) {
			return array(
				'result' => false,
				'errors' => array(array('Please select a cancellation reason')),
			);
		}

		$this->cancellation_date = date('Y-m-d H:i:s');
		$this->cancellation_reason_id = $reason_id;
		$this->cancellation_comment = $comment;
		$this->cancellation_user_id = Yii::app()->session['user']->id;

		$this->status_id = OphTrOperation_Operation_Status::model()->find('name=?',array('Cancelled'))->id;

		if (!$this->save()) {
			return array(
				'result' => false,
				'errors' => $this->getErrors()
			);
		}

		OELog::log("Operation cancelled: $this->id");

		$this->audit('operation','cancel');

		$this->event->episode->episode_status_id = 5;

		if (!$this->event->episode->save()) {
			throw new Exception('Unable to change episode status for episode '.$this->event->episode->id);
		}

		$event = $this->event;
		$event->datetime = date("Y-m-d H:i:s");
		$event->save();

		if ($this->booking) {
			$this->booking->cancellation_date = date('Y-m-d H:i:s');
			$this->booking->cancellation_reason_id = $reason_id;
			$this->booking->cancellation_comment = $comment;
			$this->booking->cancellation_user_id = Yii::app()->session['user']->id;

			if (!$this->booking->save()) {
				return array(
					'result' => false,
					'errors' => $this->booking->getErrors()
				);
			}
			OELog::log("Booking cancelled: {$this->booking->id}");

			$this->booking->audit('booking','cancel');

			if(Yii::app()->params['urgent_booking_notify_hours'] && Yii::app()->params['urgent_booking_notify_email']) {
				if(strtotime($this->booking->session_date) <= (strtotime(date('Y-m-d')) + (Yii::app()->params['urgent_booking_notify_hours'] * 3600))) {
					if (!is_array(Yii::app()->params['urgent_booking_notify_email'])) {
						$targets = array(Yii::app()->params['urgent_booking_notify_email']);
					} else {
						$targets = Yii::app()->params['urgent_booking_notify_email'];
					}
					foreach ($targets as $email) {
						mail(
							$email,
							"[OpenEyes] Urgent cancellation made","A cancellation was made with a TCI date within the next 24 hours.\n\nDisorder: "
								. $this->getDisorder() . "\n\nPlease see: http://" . @$_SERVER['SERVER_NAME']
								. Yii::app()->createUrl('transport')."\n\nIf you need any assistance you can reply to this email and one of the OpenEyes support personnel will respond.",
							"From: " . Yii::app()->params['urgent_booking_notify_email_from']."\r\n"
						);
					}
				}
			}
		}

		return array('result'=>true);
	}

	public function isEditable() {
		return $this->status->name != 'Cancelled';
	}

	public function schedule($booking_attributes, $operation_comments, $session_comments) {
		$booking = new OphTrOperation_Operation_Booking;
		$booking->attributes = $booking_attributes;

		$reschedule = in_array($this->status_id,array(2,3,4));

		preg_match('/(^[0-9]{1,2}).*?([0-9]{2})$/',$booking_attributes['admission_time'],$m);
		$booking->admission_time = $m[1].":".$m[2];

		$session = $booking->session;

		if ($this->booking) {
			// race condition, two users attempted to book the same operation at the same time
			return $this->redirect(Yii::app()->createUrl('default/view/'.$this->event_id));
		}

		foreach (array('date','start_time','end_time','theatre_id') as $field) {
			$booking->{'session_'.$field} = $booking->session->$field;
		}

		$booking->ward_id = key($this->getWardOptions($session->theatre->site_id, $session->theatre_id));

		$criteria = new CDbCriteria;
		$criteria->compare('session_id',$session->id);
		$criteria->order = 'display_order desc';
		$criteria->limit = 1;

		$booking->display_order = ($booking2 = OphTrOperation_Operation_Booking::model()->find($criteria)) ? $booking2->display_order+1 : 1;

		if (!$booking->save()) {
			die(json_encode($booking->getErrors(),true));
		}

		OELog::log("Booking ".($reschedule ? 'rescheduled' : 'made')." $booking->id");
		$booking->audit('booking',$reschedule ? 'reschedule' : 'create');

		if (!$this->erod) {
			$this->calculateEROD($session->id);
		}

		$this->event->episode->episode_status_id = 3;

		if (!$this->event->episode->save()) {
			throw new Exception('Unable to change episode status id for episode '.$this->event->episode->id);
		}

		$this->event->deleteIssues();

		if (Yii::app()->params['urgent_booking_notify_hours'] && Yii::app()->params['urgent_booking_notify_email']) {
			if (strtotime($session->date) <= (strtotime(date('Y-m-d')) + (Yii::app()->params['urgent_booking_notify_hours'] * 3600))) {
				if (!is_array(Yii::app()->params['urgent_booking_notify_email'])) {
					$targets = array(Yii::app()->params['urgent_booking_notify_email']);
				} else {
					$targets = Yii::app()->params['urgent_booking_notify_email'];
				}
				foreach ($targets as $email) {
					if ($reschedule) {
						mail($email, "[OpenEyes] Urgent reschedule made","A patient booking was rescheduled with a TCI date within the next 24 hours.\n\nDisorder: ".$this->getDisorder()."\n\nPlease see: http://".@$_SERVER['SERVER_NAME']."/transport\n\nIf you need any assistance you can reply to this email and one of the OpenEyes support personnel will respond.","From: ".Yii::app()->params['urgent_booking_notify_email_from']."\r\n");
					} else {
						mail($email, "[OpenEyes] Urgent booking made","A patient booking was made with a TCI date within the next 24 hours.\n\nDisorder: ".$this->getDisorder()."\n\nPlease see: http://".@$_SERVER['SERVER_NAME']."/transport\n\nIf you need any assistance you can reply to this email and one of the OpenEyes support personnel will respond.","From: ".Yii::app()->params['urgent_booking_notify_email_from']."\r\n");
					}
				}
			}
		}

		if ($reschedule) {
			$this->setStatus('Rescheduled');
		} else {
			$this->setStatus('Scheduled');
		}

		$this->comments = $operation_comments;
		$this->site_id = $booking->ward->site_id;

		if (!$this->save()) {
			throw new Exception('Unable to update operation data: '.print_r($this->getErrors(),true));
		}

		if ($tl = TransportList::model()->find('item_table = ? and item_id = ?',array('booking',$booking->id))) {
			if (!$tl->delete()) {
				throw new Exception('Unable to delete transport_list row: '.print_r($tl->getErrors(),true));
			}
		}

		$session->comments = $session_comments;

		if (!$session->save()) {
			throw new Exception('Unable to save session comments: '.print_r($session->getErrors(),true));
		}
	}

	public function setStatus($name) {
		if (!$status = OphTrOperation_Operation_Status::model()->find('name=?',array($name))) {
			throw new Exception('Invalid status: '.$name);
		}

		$this->status_id = $status->id;
		if (!$this->save()) {
			throw new Exception('Unable to change operation status: '.print_r($this->getErrors(),true));
		}
	}

	public function getProceduresCommaSeparated() {
		$procedures = array();
		foreach ($this->procedures as $procedure) {
			$procedures[] = $procedure->procedure->term;
		}
		return empty($procedures) ? 'No procedures' : implode(', ',$procedures);
	}

	public function getRefuseContact() {
		if ($contact = $this->getContactByType(1)) {
			throw new Exception('Unable to find refuse contact for operation '.$this->id);
		}

		if ($contact->title) {
			return $contact->title.' on '.$contact->telephone;
		}

		return $this->event->episode->firm->serviceSubspecialtyAssignment->subspecialty->name.' Admission Coordinator on '.$contact->telephone;
	}

	public function getHealthContact() {
		if ($contact = $this->getContactByType(2)) {
			return $contact->telephone;
		}
	}

	public function getContactByType($contact_type_id, $params=array()) {
		$site_id = $this->booking->ward->site_id;
		$subspecialty_id = $this->event->episode->firm->serviceSubspecialtyAssignment->subspecialty;
		$theatre_id = $this->booking->session->theatre_id;
		$firm_id = $this->event->episode->firm_id;

		foreach ($params as $key => $value) {
			${$key} = $value;
		}

		if ($contact = OphTrOperation_Letter_Contact::model()->find('contact_type_id=? and site_id=? and subspecialty_id=? and theatre_id=? and firm_id=?',array($contact_type_id,$site_id,$subspecialty_id,$theatre_id,$firm_id))) {
			return $contact;
		}
		if ($contact = OphTrOperation_Letter_Contact::model()->find('contact_type_id=? and site_id=? and theatre_id=?',array($contact_type_id,$site_id,$theatre_id))) {
			return $contact;
		}
		if ($contact = OphTrOperation_Letter_Contact::model()->find('contact_type_id=? and site_id=? and subspecialty_id=? and theatre_id=?',array($contact_type_id,$site_id,$subspecialty_id,$theatre_id))) {
			return $contact;
		}
		if ($contact = OphTrOperation_Letter_Contact::model()->find('contact_type_id=? and site_id=? and subspecialty_id=? and theatre_id is null',array($contact_type_id,$site_id,$subspecialty_id))) {
			return $contact;
		}
		if ($contact = OphTrOperation_Letter_Contact::model()->find('contact_type_id=? and site_id is null and subspecialty_id is null and theatre_id=? and firm_id=?',array($contact_type_id,$theatre_id,$firm_id))) {
			return $contact;
		}
		if ($contact = OphTrOperation_Letter_Contact::model()->find('contact_type_id=? and site_id=? and subspecialty_id is null and theatre_id is null and firm_id is null',array($contact_type_id,$site_id))) {
			return $contact;
		}

		return OphTrOperation_Letter_Contact::model()->find('site_id=? and subspecialty_id=?',array($site_id,$subspecialty_id));
	}
}
?>
