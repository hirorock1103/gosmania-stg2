<div class="table" style="margin-bottom: 10px;">
	<div class="tr"><div class="th">基本情報</div></div>
	<?php foreach($table_ary['html'] as $col => $table_info ) { ?>
		<?php if($table_info['type'] == 'string' || $table_info['type'] == 'number' ){ ?>
		<div class="tr">
			<div class="th"><?php echo $table_info['col_name']; ?></div>
			<div class="td"><?php echo $data[$col]; ?>
			<p class="input_err"><?php echo isset($err[$col]) ? $err[$col] : ''; ?></p>
			</div>
		</div>
		<?php }else if($table_info['type'] == 'textarea'){ ?>
		<div class="tr">
			<div class="th"><?php echo $table_info['col_name']; ?></div>
			<div class="td">
				<div class="pre-text-output"><?php echo nl2br($data[$col]); ?></div>
				<p class="input_err"><?php echo isset($err[$col]) ? $err[$col] : ''; ?></p>
			</div>
		</div>
		<?php }else if($table_info['type'] == 'select' || $table_info['type'] == 'radio' ){ ?>
		<div class="tr">
			<div class="th"><?php echo $table_info['col_name']; ?></div>
			<div class="td"><?php echo $table_info['arr'][$data[$col]] ?? ''; // 配列に無い値を入れられた場合は空文字で拾う ?>
			<p class="input_err"><?php echo isset($err[$col]) ? $err[$col] : ''; ?></p>
			</div>
		</div>
		<?php } ?>
		<input type="hidden" name="<?php echo $col; ?>" value="<?php echo $data[$col]; ?>">
		<input type="hidden" name="<?php echo $col; ?>" form="back_detail" value="<?php echo isset($data[$col]) ? $data[$col] : ''; ?>" >
	<?php } ?>
</div>