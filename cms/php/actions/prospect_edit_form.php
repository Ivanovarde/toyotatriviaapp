<?PHP

$template_edit_form = set('id', $record->id, $template_edit_form);
$template_edit_form = set('firstname', $record->firstname, $template_edit_form);
$template_edit_form = set('lastname', $record->lastname, $template_edit_form);
$template_edit_form = set('email', $record->email, $template_edit_form);
$template_edit_form = set('phone', $record->phone, $template_edit_form);
//$template_edit_form = set('state', $record->state, $template_edit_form);
$template_edit_form = set('city', $record->city, $template_edit_form);
$template_edit_form = set('checked', ($record->email_sent ? 'checked' : ''), $template_edit_form);

$r['status'] = true;
$r['title'] = $lang['edit_form_title'] . '(' . $record->id . ') ' . $record->firstname . ' ' . $record->lastname;
$r['html'] = $template_edit_form;
$r['msg'] = '';
$r['error'] = '';
$r['class'] = '';

