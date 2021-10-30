<?php

// DON'T FORGET THIS
if (class_exists('admin_AJAX') or die()) {
}

class ADMIN_AJAXController_Item extends admin_AJAX
{

    function submit_values()
    {
        $cjax = geoCJAX::getInstance();

        $items = $cjax->get('batch_item');

        $status = $cjax->get('batch_status');

        if ($status == '--Choose--') {
            $cjax->alert('Please choose a status.');
            return;
        }

        if (!is_array($items) || !in_array(1, $items)) {
            $cjax->alert('No items selected, please select at least one item.');
            return;
        }
        foreach ($items as $item_id => $selected) {
            if ($selected) {
                $this->changeItemStatus($item_id, $status, true);
            }
        }
    }

    public function changeItemStatus($item_id = null, $status = null, $sendEmail = null)
    {
        $cjax = geoCJAX::getInstance();
        $admin = geoAdmin::getInstance();
        if (is_array($item_id) || $item_id === null) {
            $item_id = intval($cjax->get('item_id'));
        }

        $item = $this->_getItem($item_id);

        if (!is_object($item)) {
            return false;
        }
        if ($status === null) {
            $status = trim($cjax->get('item_status'));
        }
        if ($sendEmail === null) {
            $sendEmail = intval($cjax->get('send_email'));
        }

        if (strlen($status) == 0) {
            $admin->userError('Invalid status specified!');
            $cjax->message($admin->getUserMessages(), 5);
            return false;
        }
        if ($status == 'delete') {
            //special case, delete it!
            geoOrderItem::remove($item_id);
            $admin->userSuccess('Item ' . $item_id . ' was permanently deleted.');
            $cjax->message($admin->getUserMessages());
            $cjax->wait(5);
            if ($_GET['refresh_after_delete']) {
                $cjax->load_function('geoUtil.refreshPage');
            } else {
                $cjax->location('?page=orders_list_items');
            }
            return;
        }
        $item->processStatusChange($status, $sendEmail, true);

        $admin->userSuccess('Item status for item #' . $item_id . ' changed to ' . $status . '.');
        $cjax->message($admin->getUserMessages(), 3);
        //Don't save the item till the end, in case something fails
        $item->save();

        $html = '
		<select name="item_status" id="item_status_val' . $item_id . '" class="form-control">
			<option value="active"' . (($status == 'active') ? ' selected="selected"' : '') . '>Active' . (($status == 'active') ? ' &#42;' : '') . '</option>
			<option value="pending"' . (($status == 'pending') ? ' selected="selected"' : '') . '>Pending' . (($status == 'pending') ? ' &#42;' : '') . '</option>
			<option value="declined"' . (($status == 'declined') ? ' selected="selected"' : '') . '>Declined' . (($status == 'declined') ? ' &#42;' : '') . '</option>
			<option disabled="disabled">---------</option>
			<option value="delete">Delete</option>
		</select>';

        $cjax->update('item_status' . $item_id, $html);
    }

    /**
     * Makes sure the item id is valid, if it is it returns the item object for it.
     *
     * @param int $item_id
     * @return geoOrderItem|bool
     */
    private function _getItem($item_id)
    {
        $cjax = geoCJAX::getInstance();
        $admin = geoAdmin::getInstance();
        $item_id = intval($item_id);

        if (!$item_id) {
            $admin->userError('Invalid item ID specified!');
            $cjax->message($admin->getUserMessages(), 5);
            return false;
        }
        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item) || intval($item->getId()) !== $item_id) {
            $admin->userError('Invalid item ID specified! ' . $item_id . print_r($item, 1));
            $cjax->message($admin->getUserMessages());
            return false;
        }
        return $item;
    }
}
