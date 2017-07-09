<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = 1;
include $this->admin_tpl('header', 'admin');
?>

<div class="pad-lr-10">
<form name="searchform" action="" method="get" >
<input type="hidden" value="admin" name="m">
<input type="hidden" value="withdrawals" name="c">
<input type="hidden" value="init" name="a">
<table width="100%" cellspacing="0" class="search-form">
    <tbody>
		<tr>
		<td>
		<div class="explain-col">
			关键词：
			<input name="keyword" type="text" value="<?php echo $_GET['keyword'] ? $_GET['keyword'] : '';?>"
			class="input-text" />
			<input type="submit" name="search" class="button" value="<?php echo L('search')?>" />
		</div>
		</td>
		</tr>
    </tbody>
</table>
</form>

<form id="myform" name="myform" action="?m=product&c=product&a=keyword_list_delete" method="post" >
<div class="table-list" style="position: relative;" id="threadlist">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('productid[]');"></th>
			<th width="5%" >用户id</th>
			<th width="15%" >用户昵称</th>
			<th width="15%" >支付宝账号</th>
			<th width="15%">支付宝实名</th>
			<th width="15%">电话</th>
			<th width="10%">提现金额</th>
			<th width="20%">申请时间</th>
			<th width="10%">申请ip</th>
		</tr>
	</thead>
<tbody>
<?php
if(is_array($infos)) {
	foreach ($infos as $info) {
		?>
		<tr>
			<td align="center"><input type="checkbox" name="id[]" value="<?php echo $info['id'] ?>"></td>
			<td align="center"><?php echo $info['userid']; ?>
			<td align="center"><h4><?php echo $info['nickname']; ?></h4>
			<td align="center"><h4><?php echo $info['ali_account']; ?></h4>
			<td align="center"><h4><?php echo $info['ali_username']; ?></h4>
			<td align="center"><h4><?php echo $info['phone']; ?></h4>
			<td align="center"><h4><?php echo $info['money']; ?></h4>
			<td align="center"><?php echo date('Y-m-d H:i:s', $info['addtime']); ?></td>
			<td align="center"><?php echo $info['ip']; ?></td>
		</tr>
		<?php
	}
}
?>
</tbody>
</table>

<div class="btn">

<div id="pages"><?php echo $pages?></div>
	</div>

</form>
</div>


</html>