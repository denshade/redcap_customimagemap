<?php

namespace uzgent\ImageMapClass;

// Declare your module class, which must extend AbstractExternalModule
class ImageMapClass extends \ExternalModules\AbstractExternalModule {

    const annotation = "@CUSTOM_IMAGEMAP";
    public function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
        $keyLabelCodeMap = [];

        foreach ($this->getMetadata($project_id, $instrument) as $field) {
            $field_annotation = $field['field_annotation'];
            if (strpos($field_annotation, self::annotation."=") !== false) {
                $jsCall = $this->buildJSCall($field_annotation);
                $fieldname = $field['field_name'];
                $keyLabelCodeMap[$fieldname]['label'] = html_entity_decode($field['field_label']);
                $keyLabelCodeMap[$fieldname]['script'] = "try{".$jsCall.'}catch(e) {console.warn(e);}' ;
            }
        }

        echo '<script>';
        print file_get_contents(__DIR__ . "/imagemapfunctions.js");
        print file_get_contents(__DIR__ . "/imagemap.js");
        print "\n";
        $this->activateMapScripts($keyLabelCodeMap);
        echo '</script>';
    }
    
    public function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
        $this->redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance);
    }

    function activateMapScripts(array $keyLabelCodeMap)
    {
        foreach ($keyLabelCodeMap as $key => $map)
        {
            echo $map["script"]."\n";
        }
    }

    public static function buildJSCall($field_annotation)
    {
        $field_annotation = str_replace(self::annotation . '=', "", $field_annotation);
        if (strpos($field_annotation, "imagemapfunctions.") !== false) {
            $field_annotation = str_replace("imagemapfunctions.", "imagemapfunctionsChecks.", $field_annotation);
        }
        if (strpos($field_annotation, "imagemapfunctionsChecks.") === false) {
            $field_annotation = "imagemapfunctionsChecks." . $field_annotation;
        }

        return $field_annotation;
    }
}
