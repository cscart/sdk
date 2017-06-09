<?php
namespace Tygh\Sdk\Entities;

class Addon
{
    protected $id;

    protected $root_directory_path;

    public function __construct($id, $root_directory_path)
    {
        $this->id = $id;
        $this->root_directory_path = $root_directory_path;
    }

    /**
     * @return mixed
     */
    public function getXmlSchemePath()
    {
        return "{$this->getRootDirectoryPath()}/app/addons/{$this->id}/addon.xml";
    }

    /**
     * @return mixed
     */
    public function getRootDirectoryPath()
    {
        return $this->root_directory_path;
    }

    public function getFilesGlobMasks()
    {
        $addon_files_glob_masks = [
            // General files
            "app/addons/{$this->id}",
            "var/langs/**/addons/{$this->id}.po",
            "js/addons/{$this->id}",
            "app/payments/{$this->id}.php",


            // Backend templates and assets
            "design/backend/css/addons/{$this->id}",
            "design/backend/mail/templates/addons/{$this->id}",
            "design/backend/media/images/addons/{$this->id}",
            "design/backend/media/fonts/addons/{$this->id}",
            "design/backend/templates/addons/{$this->id}",

            // Frontend templates and assets
            "design/themes/**/css/addons/{$this->id}",
            "design/themes/**/templates/addons/{$this->id}",
            "design/themes/**/layouts/addons/{$this->id}",
            "design/themes/**/mail/templates/addons/{$this->id}",
            "design/themes/**/media/images/addons/{$this->id}",
            "design/themes/**/media/images/logos/addons/{$this->id}", 
            
            "var/themes_repository/**/css/addons/{$this->id}",
            "var/themes_repository/**/templates/addons/{$this->id}",
            "var/themes_repository/**/layouts/addons/{$this->id}",
            "var/themes_repository/**/mail/templates/addons/{$this->id}",
            "var/themes_repository/**/media/images/addons/{$this->id}",
            "var/themes_repository/**/media/images/logos/addons/{$this->id}",
        ];

        if (file_exists($this->getXmlSchemePath())) {
            $addon_xml_manifest = simplexml_load_file($this->getXmlSchemePath());

            if (!empty($addon_xml_manifest->files->file)) {
                foreach ($addon_xml_manifest->files->file as $additional_file) {
                    $addon_files_glob_masks[] = $additional_file;
                }
            }
        }

        return $addon_files_glob_masks;
    }

    public function matchFilesAgainstGlobMasks($files_glob_masks, $at_directory)
    {
        $glob_matches = [];
        foreach ($files_glob_masks as $glob_mask) {
            $glob_mask = $at_directory . $glob_mask;

            foreach (glob($glob_mask) as $glob_mask_match) {
                $glob_matches[] = substr_replace($glob_mask_match, '', 0, mb_strlen($at_directory));
            }
        }

        return $glob_matches;
    }
}
