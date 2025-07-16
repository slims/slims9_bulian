<?php

/**
 * File: FormAjaxWithCustomField.php                                           *
 * Project: Form                                                               *
 * Created Date: Monday, May 19th 2025, 10:36:41 am                            *
 * Author: Waris Agung Widodo <ido.alit@gmail.com>                             *
 * -----                                                                       *
 * Last Modified: Mon May 19 2025                                              *
 * Modified By: Waris Agung Widodo                                             *
 * -----                                                                       *
 * Copyright (c) 2025 Waris Agung Widodo                                       *
 * -----                                                                       *
 * HISTORY:                                                                    *
 * Date      	By	Comments                                                   *
 * ----------	---	---------------------------------------------------------  *
 */

namespace SLiMS\Form;

class FormAjaxWithCustomField extends FormAjax
{
    use HasCustomField;

    public function __construct($str_form_name, $str_form_action, $str_form_method = 'post')
    {
        parent::__construct($str_form_name, $str_form_action, $str_form_method);
    }
}
