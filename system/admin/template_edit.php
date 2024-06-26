<div class="table" style="margin-bottom: 10px;">
	<div class="tr"><div class="th">基本情報</div></div>
	<?php foreach($table_ary['html'] as $col => $table_info ) { ?>
		<?php if($table_info['type'] == 'string' || $table_info['type'] == 'number' ){ ?>
		<div class="tr">
			<div class="th"><?php echo $table_info['col_name'].$table_info['must']; ?></div>
			<div class="td-edit">
				<input type="<?php echo $table_info['type'] == 'number' ? $table_info['type'] : 'text'; ?>" name="<?php echo $col; ?>" value="<?php echo isset($data[$col]) ? $data[$col] : ''; ?>" class="" placeholder="<?php echo $table_info['placeholder']; ?>">
			</div>
		</div>
		<?php } else if($table_info['type'] == 'textarea'){ ?>
			<div class="tr">
				<div class="th"><?php echo $table_info['col_name'].$table_info['must']; ?></div>
				<div class="td-edit">
					<textarea name="<?php echo $col; ?>" placeholder="<?php echo $table_info['placeholder']; ?>" style="width: 100%; height:300px;  resize: vertical;"><?php echo isset($data[$col]) ? $data[$col] : ''; ?></textarea>
				</div>
			</div>
		<?php }else if($table_info['type'] == 'radio') { ?>
		<div class="tr">
			<div class="th"><?php echo $table_info['col_name'].$table_info['must']; ?></div>
			<div class="td-edit">
				<div class="btn-group" data-toggle="buttons">
				<?php foreach ($table_info['arr'] as $key => $val) { ?>
					<label class="btn btn-default <?php echo ( (isset($data[$col]) && $data[$col] == $key) || ( !isset($data[$col]) && $key == 0 ) ? 'active' : ''); ?>" >
						<input type="radio" name="<?php echo $col; ?>" autocomplete="off" value="<?php echo h($key); ?>" <?php echo ( (isset($data[$col]) && $data[$col] == $key) || ( !isset($data[$col]) && $key == 0 ) ? 'checked' : ''); ?> > <?php echo h($val); ?>
					</label>
				<?php } ?>
				</div>
			</div>
		</div>
		<?php }else if($table_info['type'] == 'select') { ?>
			<div class="tr">
				<div class="th"><?php echo $table_info['col_name'].$table_info['must']; ?></div>
				<div class="td-edit">
					<div class="btn-group" data-toggle="buttons">
					<select name="<?php echo $col; ?>">
						<option value="">選択してください</option>
						<?php foreach ($table_info['arr'] as $key => $val) { ?>
							<option value="<?php echo h($key); ?>" <?php echo ( (isset($data[$col]) && $data[$col] == $key) || ( !isset($data[$col]) && $key == 0 ) ? 'selected' : ''); ?> ><?php echo h($val); ?></option>
							<!-- <label class="btn btn-default <?php echo ( (isset($data[$col]) && $data[$col] == $key) || ( !isset($data[$col]) && $key == 0 ) ? 'active' : ''); ?>" >
								<input type="radio" name="<?php echo $col; ?>" autocomplete="off" value="<?php echo h($key); ?>" <?php echo ( (isset($data[$col]) && $data[$col] == $key) || ( !isset($data[$col]) && $key == 0 ) ? 'checked' : ''); ?> > <?php echo h($val); ?>
							</label> -->
						<?php } ?>
					</select>
					</div>
				</div>
			</div>
		<?php }else if($table_info['type'] == 'hidden'){ ?>
			<input type="hidden" name="<?php echo $col; ?>" value="<?php echo isset($data[$col]) ? $data[$col] : ''; ?>" >
			<input type="hidden" name="<?php echo $col; ?>" form="back_detail" value="<?php echo isset($data[$col]) ? $data[$col] : ''; ?>" >
		<?php } ?>
	<?php } ?>
</div><!-- <div class="table"> -->
