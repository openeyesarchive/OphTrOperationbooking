<?php
/**
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
?>

<div class="accessible">
	<?php echo $this->renderPartial('letters/letter_start', array(
			'to' => $patient->salutationname,
			'patient' => $patient,
	))?>

	<p>
		<?php if ($operation->status->name == 'Rescheduled') {?>
			I am writing to inform you that the date for your <?php echo $operation->textOperationName?> has been changed<?php if (isset($operation->cancelledBookings[0])) {?> from <?php echo date('jS F Y',strtotime($operation->cancelledBookings[0]->date))}?>, the new details are:
		<?php }else{?>
			I am pleased to confirm the date of your <?php echo $operation->textOperationName?> with <?php echo $firm->consultantName?>, the details are:
		<?php }?>
	</p>

	<table class="borders">
		<tr>
			<th>Date of admission:</th>
			<td><?php echo date('jS F Y', strtotime($operation->booking->session->date))?></td>
		</tr>
		<tr>
			<th>Time to arrive:</th>
			<td><?php echo date('g:ia',strtotime($operation->booking->admission_time))?></td>
		</tr>
		<tr>
			<th>Ward:</th>
			<td>
				<?php echo $operation->booking->ward->longName?>
			</td>
		</tr>
		<tr>
			<th>Location:</th>
			<td><?php echo CHtml::encode($site->name)?></td>
		</tr>
		<tr>
			<th>Consultant:</th>
			<td><?php echo $firm->consultantName?></td>
		</tr>
		<tr>
			<th>Speciality:</th>
			<td><?php echo $firm->serviceSubspecialtyAssignment->subspecialty->name?></td>
		</tr>
	</table>
	<p></p>

	<?php if (!$patient->isChild()) {?>
		<p>
			If this is not convenient or you no longer wish to proceed with surgery, please contact the <?php echo $operation->refuseContact?> as soon as possible.
		</p>

		<?php if (!$operation->overnight_stay) {?>
			<p>
				<em>This is a daycase and you will be discharged from hospital on the same day.</em>
			</p>
		<?php }?>

		<?php if ($operation->booking->session->showPreopWarning()) {?>
			<p>
				<strong>
					All admissions require a Pre-Operative Assessment which you must attend. Non-attendance will cause a delay or possible <em>cancellation</em> to your surgery.
				</strong>
			</p>
		<?php }?>

		<?php if ($operation->showPrescriptionWarning()) {?>
			<p>
				<em>You may be given a prescription after your treatment. This can be collected from our pharmacy on the ward, however unless you have an exemption certificate the standard prescription charge will apply.	Please ensure you have the correct money or ask the relative/friend/carer who is collecting you to make sure they bring some money to cover the prescription.</em>
			</p>
		<?php }?>
	<?php }?>

	<p>To help ensure your admission proceeds smoothly, please follow these instructions:</p>

	<ul>
		<?php if ($operation->textAdmissionInstructionWarning) {?>
			<li>
				<strong><?php echo $operation->textAdmissionInstructionWarning?></strong>
			</li>
		<?php }?>
		<li>
			Bring this letter with you on date of admission
		</li>
		<li>
			Please go directly to <?php echo $operation->booking->ward->directionsText?>
			<?php if ($patient->isChild()) {?>
				at the time of your child's admission
			<?php }?>
		</li>
		<?php if (!$patient->isChild()) {?>
			<li>
				You must not drive yourself to or from hospital
			</li>
			<?php if ($operation->showSeatingWarning()) {?>
				<li>
					We would like to request that only 1 person should accompany you in order to ensure that adequate seating is available for patients
				</li>
			<?php }?>
			<?php if ($operation->showPrescriptionWarning()) {?>
				<li>
					<em>Check whether you have to pay or are exempt from prescription charges. If you are exempt, you will need to provide proof that you are exempt every time you collect a prescription. The prescription charge is £7.40 per item.</em>
				</li>
			<?php }?>
		<?php }?>
	</ul>

	<?php if ($patient->isChild()) {?>
		<p>
			If there has been any change in your child's general health, such as a cough or cold, any infectious disease, or any other condition which might affect their fitness for operation, please telephone <?php echo $operation->textChildUnwellPreopNumber?> for advice.
		</p>
	<?php }else{?>
		<p>
			If you are unwell the day before admission, please contact us to ensure that it is still safe and appropriate to do the procedure.
		</p>
	<?php }?>

	<p>
		If you do not speak English, please arrange for an English speaking adult to stay with you until you reach the ward and have been seen by a doctor and anaesthetist.
	</p>

	<?php if ($patient->isChild()) {?>
		<p>
			It is very important that you let us know immediately if you are unable to keep this admission date. Please let us know by return of post, or if necessary, telephone <?php echo $operation->textPaediatricAdmissionCoordinator?>
		</p>
	<?php }?>

	<?php echo $this->renderPartial('letters/letter_end')?>
</div>
