<?php
// $Id$
/**
 * @file views-ui-edit-item.tpl.php
 *
 * This template handles the printing of fields/filters/sort criteria/arguments or relationships.
 */
?>
<?php print $rearrange; ?>
<?php print $add; ?>
<div class="views-category-title" <?php if ($overridden) { print ' class="overridden"'; }?>>
  <?php print $title; ?>
</div>  

<?php if (empty($fields)): ?>
  <div><?php print t('None defined'); ?></div>
<?php else: ?>
  <?php foreach ($fields as $field): ?>
    <?php if (!empty($fields['links'])): ?>
      <?php print $field['links']; ?>
    <?php endif; ?>
    <div class="<?php print $field['class']; ?>">
      <?php print $field['title']; ?>
      <?php print $field['info']; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
