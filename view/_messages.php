    <div id="flash-messages">
    <?php
    if (isset(JRP::$flash_messages) and is_array(JRP::$flash_messages)) {
        foreach(JRP::$flash_messages as $idx=>$message) {
            if ($message['display'] == 'local') {
                switch ($message['type']) {
                    case JRP::MSG_ERROR:
                        ?>
                        <div class="jrp-msg-error">
                            <span class="ui-icon ui-icon-alert" style="float:left"></span>
                            &nbsp;<strong>ERROR:</strong>
                            <?php echo $message['text']; ?>
                            <span class="ui-icon ui-icon-closethick close-message" style="float:right" title="Close message"></span>
                        </div>
                        <?php
                        break;
                    case JRP::MSG_WARNING:
                        ?>
                        <div class="jrp-msg-warning">
                            <span class="ui-icon ui-icon-alert" style="float:left"></span>
                            &nbsp;<strong>WARNING:</strong>
                            <?php echo $message['text']; ?>
                            <span class="ui-icon ui-icon-closethick close-message" style="float:right" title="Close message"></span>
                        </div>
                        <?php
                        break;
                    case JRP::MSG_SUCCESS:
                        ?>
                        <div class="jrp-msg-success">
                            <span class="ui-icon ui-icon-circle-check" style="float:left"></span>
                            &nbsp;<strong>SUCCESS:</strong>
                            <?php echo $message['text']; ?>
                            <span class="ui-icon ui-icon-closethick close-message" style="float:right" title="Close message"></span>
                        </div>
                        <?php
                        break;
                    case JRP::MSG_INFO:
                    default:
                        ?>
                        <div class="jrp-msg-info">
                            <span class="ui-icon ui-icon-info" style="float:left"></span>
                            &nbsp;
                            <?php echo $message['text']; ?>
                            <span class="ui-icon ui-icon-closethick close-message" style="float:right" title="Close message"></span>
                        </div>
                        <?php
                        break;
                }
            }
        }
    }
    ?>
    </div>
