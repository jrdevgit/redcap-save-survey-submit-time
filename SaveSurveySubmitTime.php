<?php

namespace MCRI\SaveSurveySubmitTime;

use ExternalModules\AbstractExternalModule;

class SaveSurveySubmitTime extends AbstractExternalModule {
        
        public function redcap_save_record($project_id, $record=null, $instrument, $event_id, $group_id=null, $survey_hash=null, $response_id=null, $repeat_instance=1) {

                if (is_null($survey_hash)) { return; } // not a survey submit

                if (!($_POST['submit-action']==='submit-btn-savereturnlater' ||
                      ($_POST['submit-action']==='submit-btn-saverecord' && $_POST[$instrument.'_complete']=='2'))
                    ) {
                        return;
                }

                $instrument_settings = $this->getSubSettings('instrument_settings');
                foreach($instrument_settings as $setting) {
                        if ($setting['logtime_instrument'] == $instrument) {
                                $logTimeField = $setting['logtime_field'];
                                $timeNow = date('Y-m-d H:i:s');

                                $saveResult = \REDCap::saveData(
                                        'array', 
                                        array($record => array($event_id => array($logTimeField => $timeNow)))
                                );

                                if (count($saveResult['errors'])>0) {
                                        $title = "ERROR in LogSurveySubmitTime external module: could not save value to field";
                                        $detail = "record=$record, event=$event_id, field=$logTimeField, value=$timeNow <br>saveResult=".print_r($saveResult, true);
                                        \REDCap::logEvent($title, $detail, '', $record, $event_id);
                                        $this->sendAdminEmail($title, $detail);
                                }
                        }
                }
	}
}