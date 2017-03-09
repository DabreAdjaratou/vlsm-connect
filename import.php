<?php
session_start();
ob_start();
include('includes/MysqliDb.php');
include('includes/General.php');
$general = new Deforay_Commons_General();
$tableName = "vl_request_form";
if(PHP_SAPI === 'cli'){
  $param = $argv[1];
}else{
  $param = $_GET['type'];
}
try {
  //Import request/result xml
  $formQuery ="SELECT value FROM global_config where name='vl_form'";
  $formResult = $db->rawQuery($formQuery);
  $country = $formResult[0]['value'];
  $configQuery ="SELECT value FROM global_config where name='sync_path'";
  $configResult = $db->rawQuery($configQuery);
  if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= '' && file_exists($configResult[0]['value'])){
      $files = array();
      if(isset($param) && $param == 'request'){
          $files = scandir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new");
      }else if(isset($param) && $param == 'result'){
          $files = scandir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new");
      }
      if(count($files) >2){
          foreach($files as $file) {
            if (in_array($file, array(".",".."))) continue;
            if(isset($param) && $param == 'request'){
                $xmlFile = file_get_contents($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
            }else if(isset($param) && $param == 'result'){
               $xmlFile = file_get_contents($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);  
            }
            $xml = new SimpleXMLElement($xmlFile);
            if(count($xml->vl_request_form) >0){
              foreach($xml->vl_request_form as $val){
                if(isset($val->form_id) && (string)$val->form_id == $country){
                  $data = array();
                  if(isset($val->sample_code)){
                      $data['sample_code']=(string)$val->sample_code;
                  }
                  //print_r($data);die;
                  if(isset($val->vl_instance_id)){
                    $data['vl_instance_id']=(string)$val->vl_instance_id;
                  }
                
                  if(isset($val->serial_no)){
                    $data['serial_no']=(string)$val->serial_no;
                  }
                 
                  if(isset($val->facility_name)){
                    $clinicQuery = 'select facility_id from facility_details where facility_name = "'.(string)$val->facility_name.'"';
                    $clinicResult = $db->rawQuery($clinicQuery);
                    if(isset($clinicResult[0]['facility_id'])){
                      $data['facility_id'] = $clinicResult[0]['facility_id'];
                    }
                    else{
                      $facilityCode=NULL;
                      $facility_contact_person=NULL;
                      $facility_phone_number=NULL;
                      $facility_address=NULL;
                      $facility_country=NULL;
                      $facility_state=NULL;
                      $facility_district=NULL;
                      $facility_hub_name=NULL;
                      $facility_other_id=NULL;
                      $facility_longitude=NULL;
                      $facility_latitude=NULL;
                      $facility_email=NULL;
                      if(isset($val->facility_code)){
                          $facilityCode = (string)$val->facility_code;
                      }
                      if(isset($val->facility_contact_person)){
                          $facility_contact_person=(string)$val->facility_contact_person;
                      }
                      if(isset($val->facility_phone_number)){
                          $facility_phone_number=(string)$val->facility_phone_number;
                      }
                      if(isset($val->facility_address)){
                          $facility_address=(string)$val->facility_address;
                      }
                      if(isset($val->facility_country)){
                          $facility_country=(string)$val->facility_country;
                      }
                      if(isset($val->facility_state)){
                          $facility_state=(string)$val->facility_state;
                      }
                      if(isset($val->facility_district)){
                          $facility_district=(string)$val->facility_district;
                      }
                      if(isset($val->facility_hub_name)){
                          $facility_hub_name=(string)$val->facility_hub_name;
                      }
                      if(isset($val->other_id)){
                          $facility_other_id=(string)$val->other_id;
                      }
                      if(isset($val->facility_longitude)){
                          $facility_longitude=(string)$val->facility_longitude;
                      }
                      if(isset($val->facility_latitude)){
                          $facility_latitude=(string)$val->facility_latitude;
                      }
                      if(isset($val->facility_email)){
                          $facility_email=(string)$val->facility_email;
                      }
                      $clinicData = array(
                        'facility_name'=>(string)$val->facility_name,
                        'facility_code'=>$facilityCode,
                        'other_id'=>$facility_other_id,
                        'contact_person'=>$facility_contact_person,
                        'phone_number'=>$facility_phone_number,
                        'address'=>$facility_address,
                        'country'=>$facility_country,
                        'state'=>$facility_state,
                        'district'=>$facility_district,
                        'hub_name'=>$facility_hub_name,
                        'latitude'=>$facility_latitude,
                        'longitude'=>$facility_longitude,
                        'email'=>$facility_email,
                        'facility_type'=>1,
                        'status'=>'active'
                      );
                      $id = $db->insert('facility_details',$clinicData);
                      $data['facility_id'] = $id;
                    }
                  }
                
                  if(isset($val->sample_type)){
                      $data['sample_id'] = NULL;
                      $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.(string)$val->sample_type.'"';
                      $specimenResult = $db->rawQuery($specimenTypeQuery);
                      if(isset($specimenResult[0]['sample_id'])){
                         $data['sample_id'] = $specimenResult[0]['sample_id'];
                      }else{
                         $sampleTypeData = array(
                                           'sample_name'=>(string)$val->sample_type,
                                           'status'=>'active'
                                        );
                         $id = $db->insert('r_sample_type',$sampleTypeData);
                         $data['sample_id'] = $id;
                      }
                  }
                
                  if(isset($val->testing_status)){
                      $data['status'] = NULL;
                      $statusQuery = 'select status_id from testing_status where status_name = "'.(string)$val->testing_status.'" OR status_name = "'.strtolower((string)$val->testing_status).'"';
                      $statusResult = $db->rawQuery($statusQuery);
                      if(isset($statusResult[0]['status_id'])){
                         $data['status'] = $statusResult[0]['status_id'];
                      }else{
                        $tStatusData = array(
                                           'status_name'=>(string)$val->testing_status
                                        );
                        $id = $db->insert('testing_status',$tStatusData);
                        $data['status'] = $id;
                      }
                  }
                
                  //if(isset($val->nation_identifier)){
                  //  $data['nation_identifier'] = (string)$val->nation_identifier;
                  //}
                  
                  if(isset($val->batch_code)){
                      $data['lab_id'] = NULL;
                      $batch_code_key=NULL;
                      $batch_status=NULL;
                      
                      $batchQuery = 'select batch_id from batch_details where batch_code = "'.(string)$val->batch_code.'"';
                      $batchResult = $db->rawQuery($batchQuery);
                      if(isset($batchResult[0]['batch_id'])){
                       $data['batch_id'] = $batchResult[0]['batch_id'];
                      }else{
                          if(isset($val->batch_code_key)){
                              $batch_code_key = (string)$val->batch_code_key;
                          }
                          if(isset($val->batch_status)){
                            $batch_status = (string)$val->batch_status;
                          }
                          $batchData = array(
                                          'batch_code'=>(string)$val->batch_code,
                                          'batch_code_key'=>$batch_code_key,
                                          'batch_status'=>$batch_status
                                      );
                         $id = $db->insert('batch_details',$batchData);
                         $data['batch_id'] = $id;
                      }
                  }
                  
                  if(isset($val->urgency)>0){
                    $data['urgency'] = strtolower((string)$val->urgency);
                  }
                  if(isset($val->patient_name)){
                    $data['patient_name'] = (string)$val->patient_name;
                  }
                  if(isset($val->surname)){
                    $data['surname'] = (string)$val->surname;
                  }
                  if(isset($val->art_no)){
                    $data['art_no'] = (string)$val->art_no;
                  }
                  if(isset($val->patient_dob)){
                    $data['patient_dob'] = (string)$val->patient_dob;
                  }
                  if(isset($val->gender)){
                    $data['gender'] = (string)$val->gender;
                  }
                  if(isset($val->patient_phone_number)){
                    $data['patient_phone_number'] = (string)$val->patient_phone_number;
                  }
                  if(isset($val->location)){
                    $data['location'] = (string)$val->location;
                  }
                  if(isset($val->patient_art_date)){
                    $data['patient_art_date'] = (string)$val->patient_art_date;
                  }
                  if(isset($val->sample_collection_date)){
                    $data['sample_collection_date'] = (string)$val->sample_collection_date;
                  }
                  if(isset($val->is_patient_new)){
                    $data['is_patient_new'] = (string)$val->is_patient_new;
                  }
                  if(isset($val->treatment_initiation)){
                    $data['treatment_initiation'] = (string)$val->treatment_initiation;
                  }
                  if(isset($val->current_regimen)){
                    $data['current_regimen'] = (string)$val->current_regimen;
                  }
                  if(isset($val->date_of_initiation_of_current_regimen)){
                    $data['date_of_initiation_of_current_regimen'] = (string)$val->date_of_initiation_of_current_regimen;
                  }
                  if(isset($val->is_patient_pregnant)){
                    $data['is_patient_pregnant'] = (string)$val->is_patient_pregnant;
                  }
                  if(isset($val->is_patient_breastfeeding)){
                    $data['is_patient_breastfeeding'] = (string)$val->is_patient_breastfeeding;
                  }
                  if(isset($val->trimestre)){
                    $data['trimestre'] = (string)$val->trimestre;
                  }
                  if(isset($val->arv_adherence)){
                    $data['arv_adherence'] = (string)$val->arv_adherence;
                  }
                  if(isset($val->patient_receive_sms)){
                    $data['patient_receive_sms'] = (string)$val->patient_receive_sms;
                  }
                  if(isset($val->viral_load_indication)){
                    $data['viral_load_indication'] = (string)$val->viral_load_indication;
                  }
                  if(isset($val->enhance_session)){
                    $data['enhance_session'] = (string)$val->enhance_session;
                  }
                  if(isset($val->routine_monitoring_last_vl_date)){
                    $data['routine_monitoring_last_vl_date'] = (string)$val->routine_monitoring_last_vl_date;
                  }
                  if(isset($val->routine_monitoring_sample_type)){
                    $data['routine_monitoring_sample_type'] = (string)$val->routine_monitoring_sample_type;
                  }
                  if(isset($val->vl_treatment_failure_adherence_counseling_last_vl_date)){
                    $data['vl_treatment_failure_adherence_counseling_last_vl_date'] = (string)$val->vl_treatment_failure_adherence_counseling_last_vl_date;
                  }
                  if(isset($val->vl_treatment_failure_adherence_counseling_value)){
                    $data['vl_treatment_failure_adherence_counseling_value'] = (string)$val->vl_treatment_failure_adherence_counseling_value;
                  }
                  if(isset($val->vl_treatment_failure_adherence_counseling_sample_type)){
                    $data['vl_treatment_failure_adherence_counseling_sample_type'] = (string)$val->vl_treatment_failure_adherence_counseling_sample_type;
                  }
                  if(isset($val->suspected_treatment_failure_last_vl_date)){
                    $data['suspected_treatment_failure_last_vl_date'] = (string)$val->suspected_treatment_failure_last_vl_date;
                  }
                  if(isset($val->suspected_treatment_failure_value)){
                    $data['suspected_treatment_failure_value'] = (string)$val->suspected_treatment_failure_value;
                  }
                  if(isset($val->suspected_treatment_failure_sample_type)){
                    $data['suspected_treatment_failure_sample_type'] = (string)$val->suspected_treatment_failure_sample_type;
                  }
                  if(isset($val->switch_to_tdf_last_vl_date)){
                    $data['switch_to_tdf_last_vl_date'] = (string)$val->switch_to_tdf_last_vl_date;
                  }
                  if(isset($val->switch_to_tdf_value)){
                    $data['switch_to_tdf_value'] = (string)$val->switch_to_tdf_value;
                  }
                  if(isset($val->switch_to_tdf_sample_type)){
                    $data['switch_to_tdf_sample_type'] = (string)$val->switch_to_tdf_sample_type;
                  }
                  if(isset($val->missing_last_vl_date)){
                    $data['missing_last_vl_date'] = (string)$val->missing_last_vl_date;
                  }
                  if(isset($val->missing_value)){
                    $data['missing_value'] = (string)$val->missing_value;
                  }
                  if(isset($val->missing_sample_type)){
                    $data['missing_sample_type'] = (string)$val->missing_sample_type;
                  }
                  if(isset($val->request_clinician)){
                    $data['request_clinician'] = (string)$val->request_clinician;
                  }
                  if(isset($val->clinician_ph_no)){
                    $data['clinician_ph_no'] = (string)$val->clinician_ph_no;
                  }
                  if(isset($val->sample_testing_date)){
                    $data['sample_testing_date'] = (string)$val->sample_testing_date;
                  }
                  if(isset($val->vl_focal_person)){
                    $data['vl_focal_person'] = (string)$val->vl_focal_person;
                  }
                  if(isset($val->focal_person_phone_number)){
                    $data['focal_person_phone_number'] = (string)$val->focal_person_phone_number;
                  }
                  if(isset($val->email_for_HF)){
                    $data['email_for_HF'] = (string)$val->email_for_HF;
                  }
                  if(isset($val->date_sample_received_at_testing_lab)){
                    $data['date_sample_received_at_testing_lab'] = (string)$val->date_sample_received_at_testing_lab;
                  }
                  if(isset($val->date_results_dispatched)){
                    $data['date_results_dispatched'] = (string)$val->date_results_dispatched;
                  }
                  if(isset($val->rejection)){
                    $data['rejection'] = (string)$val->rejection;
                  }
                  if(isset($val->sample_rejection_facility)){
                    $data['sample_rejection_facility'] = (string)$val->sample_rejection_facility;
                  }
                
                  if(isset($val->sample_rejection_reason)){
                      $rrQuery = 'select rejection_reason_id from r_sample_rejection_reasons where rejection_reason_name = "'.(string)$val->sample_rejection_reason.'" or rejection_reason_name = "'.strtolower((string)$val->sample_rejection_reason).'"';
                      $rrResult = $db->rawQuery($rrQuery);
                      if(isset($rrResult[0]['rejection_reason_id'])){
                         $data['sample_rejection_reason'] = $rrResult[0]['rejection_reason_id'];
                      }else{
                          $rrData = array(
                                          'rejection_reason_name'=>(string)$val->sample_rejection_reason,
                                          'rejection_reason_status'=>'active'
                                  );
                          $id = $db->insert('r_sample_rejection_reasons',$rrData);
                          $data['sample_rejection_reason'] = $id;
                      }
                  }
                
                  if(isset($val->other_id)){
                    $data['other_id'] = (string)$val->other_id;
                  }
                  if(isset($val->age_in_yrs)){
                    $data['age_in_yrs'] = (string)$val->age_in_yrs;
                  }
                  if(isset($val->age_in_mnts)){
                    $data['age_in_mnts'] = (string)$val->age_in_mnts;
                  }
                  if(isset($val->treatment_initiated_date)){
                    $data['treatment_initiated_date'] = (string)$val->treatment_initiated_date;
                  }
                  if(isset($val->arc_no)){
                    $data['arc_no'] = (string)$val->arc_no;
                  }
                  if(isset($val->treatment_details)){
                    $data['treatment_details'] = (string)$val->treatment_details;
                  }
                
                  if(isset($val->lab_name)){
                    $data['lab_id'] = NULL;
                    $labQuery = 'select facility_id from facility_details where facility_name = "'.(string)$val->lab_name.'"';
                    $labResult = $db->rawQuery($labQuery);
                    if(isset($labResult[0]['facility_id'])){
                       $data['lab_id'] = $labResult[0]['facility_id'];
                    }else{
                       $labData = array(
                                         'facility_name'=>(string)$val->lab_name,
                                         'facility_type'=>2,
                                         'status'=>'active'
                                     );
                       $id = $db->insert('facility_details',$labData);
                       $data['lab_id'] = $id;
                    }
                  }
                
                  if(isset($val->lab_no)){
                    $data['lab_no'] = (string)$val->lab_no;
                  }
                  if(isset($val->lab_contact_person)){
                    $data['lab_contact_person'] = (string)$val->lab_contact_person;
                  }
                  if(isset($val->lab_phone_no)){
                    $data['lab_phone_no'] = (string)$val->lab_phone_no;
                  }
                  if(isset($val->lab_tested_date)){
                    $data['lab_tested_date'] = (string)$val->lab_tested_date;
                  }
                  if(isset($val->justification)){
                    $data['justification'] = (string)$val->justification;
                  }
                  if(isset($val->log_value)){
                    $data['log_value'] = (string)$val->log_value;
                  }
                  if(isset($val->absolute_value)){
                    $data['absolute_value'] = (string)$val->absolute_value;
                  }
                  if(isset($val->text_value)){
                    $data['text_value'] = (string)$val->text_value;
                  }
                  if(isset($val->result)){
                    $data['result'] = (string)$val->result;
                  }
                  if(isset($val->comments)){
                    $data['comments'] = (string)$val->comments;
                  }
                  if(isset($val->result_reviewed_date)){
                    $data['result_reviewed_date'] = (string)$val->result_reviewed_date;
                  }
                  if(isset($val->test_methods)){
                    $data['test_methods'] = (string)$val->test_methods;
                  }
                  if(isset($val->contact_complete_status)){
                    $data['contact_complete_status'] = (string)$val->contact_complete_status;
                  }
                  if(isset($val->last_viral_load_date)){
                    $data['last_viral_load_date'] = (string)$val->last_viral_load_date;
                  }
                  if(isset($val->last_viral_load_result)){
                    $data['last_viral_load_result'] = (string)$val->last_viral_load_result;
                  }
                  if(isset($val->viral_load_log)){
                    $data['viral_load_log'] = (string)$val->viral_load_log;
                  }
                  if(isset($val->vl_test_reason)){
                    $data['vl_test_reason'] = (string)$val->vl_test_reason;
                  }
                  if(isset($val->drug_substitution)){
                    $data['drug_substitution'] = (string)$val->drug_substitution;
                  }
                  if(isset($val->vl_test_platform)){
                    $data['vl_test_platform'] = (string)$val->vl_test_platform;
                  }
                  if(isset($val->support_partner)){
                    $data['support_partner'] = (string)$val->support_partner;
                  }
                  if(isset($val->has_patient_changed_regimen)){
                    $data['has_patient_changed_regimen'] = (string)$val->has_patient_changed_regimen;
                  }
                  if(isset($val->reason_for_regimen_change)){
                    $data['reason_for_regimen_change'] = (string)$val->reason_for_regimen_change;
                  }
                  if(isset($val->date_of_regimen_changed)){
                    $data['date_of_regimen_changed'] = (string)$val->date_of_regimen_changed;
                  }
                  if(isset($val->plasma_conservation_temperature)){
                    $data['plasma_conservation_temperature'] = (string)$val->plasma_conservation_temperature;
                  }
                  if(isset($val->duration_of_conservation)){
                    $data['duration_of_conservation'] = (string)$val->duration_of_conservation;
                  }
                  if(isset($val->date_of_demand)){
                    $data['date_of_demand'] = (string)$val->date_of_demand;
                  }
                  if(isset($val->viral_load_no)){
                    $data['viral_load_no'] = (string)$val->viral_load_no;
                  }
                  if(isset($val->date_dispatched_from_clinic_to_lab)){
                    $data['date_dispatched_from_clinic_to_lab'] = (string)$val->date_dispatched_from_clinic_to_lab;
                  }
                  if(isset($val->date_of_completion_of_viral_load)){
                    $data['date_of_completion_of_viral_load'] = (string)$val->date_of_completion_of_viral_load;
                  }
                  if(isset($val->date_result_printed)){
                    $data['date_result_printed'] = (string)$val->date_result_printed;
                  }
                  if(isset($val->result_coming_from)){
                    $data['result_coming_from'] = (string)$val->result_coming_from;
                  }
                  if(isset($val->form_id)){
                     $data['form_id'] = (string)$val->form_id;
                  }else{
                    $data['form_id'] = $country;
                  }
                  if(isset($val->test_request_export)){
                     $data['test_request_export'] = (string)$val->test_request_export;
                  }
                  if(isset($param) && $param == 'request'){
                     $data['test_request_import'] = 1;
                  }else{
                    if(isset($val->test_request_import)){
                       $data['test_request_import'] = (string)$val->test_request_import;
                    }
                  }
                  if(isset($val->test_result_export)){
                     $data['test_result_export'] = (string)$val->test_result_export;
                  }
                  if(isset($param) && $param == 'result'){
                     $data['test_result_import'] = 1;
                  }else{
                    if(isset($val->test_result_import)){
                       $data['test_result_import'] = (string)$val->test_result_import;
                    }
                  }
                  //print_r($data);die;
                  $sampleQuery = 'select vl_sample_id from vl_request_form where sample_code = "'.(string)$val->sample_code.'"';
                  $sampleResult = $db->rawQuery($sampleQuery);
                  if(isset($sampleResult[0]['vl_sample_id'])){
                     $db=$db->where('sample_code',(string)$val->sample_code);
                     $db->update($tableName,$data);
                     if(isset($param) && $param == 'request'){
                        //Update node element
                        $info = simplexml_load_file($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . $file);
                        $info->vl_request_form->test_request_import = 1;
                        $info->asXML($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . $file);
                     }else if(isset($param) && $param == 'result'){
                        //Update node element
                        $info = simplexml_load_file($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
                        $info->vl_request_form->test_result_import = 1;
                        $info->asXML($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
                         //move updated new xml file
                        copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml');
                        unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml');
                     }
                  }else{
                     if(isset($param) && $param == 'request'){
                        $data['created_by'] = 1;
                        $data['created_on'] = $general->getDateTime();
                        $db->insert($tableName,$data);
                        //Update node element
                        $info = simplexml_load_file($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
                        $info->vl_request_form->test_request_import = 1;
                        $info->asXML($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
                        //move updated new xml file
                        copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml');
                        unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml');
                     }else if(isset($param) && $param == 'result'){
                        copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml');
                        unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$val->sample_code.'.xml');
                     }
                  }
                }
              }
            }
          }
      }else{
        if(isset($param) && $param == 'result'){
          $files = scandir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced");
          foreach($files as $file) {
             if(in_array($file, array(".",".."))) continue;
             $sampleCode = explode(".",$file);
             $vlQuery="SELECT sample_code FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN r_art_code_details as art ON vl.current_regimen=art.art_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id WHERE vl.sample_code = '".$sampleCode[0]."' AND vl.test_result_import = 0";
             $vlResult = $db->rawQuery($vlQuery);
             if(isset($vlResult[0]['sample_code'])){
                $db=$db->where('sample_code',$vlResult[0]['sample_code']);
                $db->update($tableName,array('test_result_import'=>1));
             }
          }
        }
      }
  }
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}