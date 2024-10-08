<?php

namespace App\Controllers;

class Checklist_groups extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
    }

    //load checklist group list view
    function index() {
        return $this->template->view("checklist_groups/index");
    }

    //load checklist group add/edit modal form
    function modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $checklists = $this->Checklist_template_model->get_all_where(array("deleted" => 0))->getResult();
        $suggestion = array();

        foreach ($checklists as $checklist) {
            $suggestion[] = array("id" => $checklist->id, "text" => $checklist->title);
        }

        $view_data['checklists_dropdown'] = json_encode($suggestion);
        $view_data['model_info'] = $this->Checklist_groups_model->get_one($this->request->getPost('id'));
        return $this->template->view('checklist_groups/modal_form', $view_data);
    }

    //save checklist group 
    function save() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "checklists" => "required"
        ));

        $id = $this->request->getPost('id');
        $checklists = $this->request->getPost('checklists');
        validate_list_of_numbers($checklists);

        $data = array(
            "title" => $this->request->getPost('title'),
            "checklists" => $checklists
        );
        $save_id = $this->Checklist_groups_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //delete/undo checklist group 
    function delete() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Checklist_groups_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Checklist_groups_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    //get data for checklist group list
    function list_data() {
        $list_data = $this->Checklist_groups_model->get_details()->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get checklist group list row
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Checklist_groups_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    //prepare checklist group list row
    private function _make_row($data) {
        $total_checklists = "<span class='badge badge-light w100'><i data-feather='check-circle' class='icon-16'></i> " . count(explode(",", $data->checklists)) . "</span>";

        return array(
            $data->title,
            modal_anchor(get_uri("checklist_groups/checklists_list"), $total_checklists, array("title" => app_lang('checklists'), "data-post-checklists" => $data->checklists)),
            modal_anchor(get_uri("checklist_groups/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_checklist_group'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_checklist_group'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("checklist_groups/delete"), "data-action" => "delete"))
        );
    }

    function checklists_list() {
        $view_data['checklists'] = $this->Checklist_template_model->get_checklists($this->request->getPost('checklists'))->getResult();
        return $this->template->view('checklist_groups/checklists_list', $view_data);
    }
}

/* End of file Task_checklist_groups.php */
/* Location: ./app/controllers/Task_checklist_groups.php */