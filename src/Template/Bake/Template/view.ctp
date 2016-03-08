<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Utility\Inflector;

$associations += ['BelongsTo' => [], 'HasOne' => [], 'HasMany' => [], 'BelongsToMany' => []];
$immediateAssociations = $associations['BelongsTo'];
$associationFields = collection($fields)
    ->map(function($field) use ($immediateAssociations) {
        foreach ($immediateAssociations as $alias => $details) {
            if ($field === $details['foreignKey']) {
                return [$field => $details];
            }
        }
    })
    ->filter()
    ->reduce(function($fields, $value) {
        return $fields + $value;
    }, []);

$groupedFields = collection($fields)
    ->filter(function($field) use ($schema) {
        return $schema->columnType($field) !== 'binary';
    })
    ->groupBy(function($field) use ($schema, $associationFields) {
        $type = $schema->columnType($field);
        if (isset($associationFields[$field])) {
            return 'string';
        }
        if (in_array($type, ['integer', 'float', 'decimal', 'biginteger'])) {
            return 'number';
        }
        if (in_array($type, ['date', 'time', 'datetime', 'timestamp'])) {
            return 'date';
        }
        return in_array($type, ['text', 'boolean']) ? $type : 'string';
    })
    ->toArray();

$groupedFields += ['number' => [], 'string' => [], 'boolean' => [], 'date' => [], 'text' => []];
$pk = "\$$singularVar->{$primaryKey[0]}";
%>
<nav class="col-lg-2 col-md-3 columns" id="actions-sidebar">
    <div class="btn-group-vertical text-left">
        <?= $this->Html->link(__('<i class="fa fa-pencil"></i> Edit <%= $singularHumanName %>'), ['action' => 'edit', <%= $pk %>], ['class' => 'btn btn-warning btn-sm', 'title' => 'Edit <%= $singularHumanName %>', 'data-toggle' => 'tooltip', 'escape' => false]) ?> 
        <?= $this->Form->postLink(__('<i class="fa fa-trash-o"></i> Delete <%= $singularHumanName %>'), ['action' => 'delete', <%= $pk %>], ['class' => 'btn btn-danger btn-sm', 'title' => 'Delete <%= $singularHumanName %>', 'data-toggle' => 'tooltip', 'escape' => false, 'confirm' => __('Are you sure you want to delete # {0}?', <%= $pk %>)]) ?> 
        <?= $this->Html->link(__('<i class="fa fa-list-alt"></i> List <%= $pluralHumanName %>'), ['action' => 'index'], ['class' => 'btn btn-primary btn-sm', 'title' => 'List <%= $pluralHumanName %>', 'data-toggle' => 'tooltip', 'escape' => false]) ?> 
        <?= $this->Html->link(__('<i class="fa fa-plus"></i> New <%= $singularHumanName %>'), ['action' => 'add'], ['class' => 'btn btn-success btn-sm', 'title' => 'New <%= $singularHumanName %>', 'data-toggle' => 'tooltip', 'escape' => false]) ?> 
<%
    $done = [];
    foreach ($associations as $type => $data) {
        foreach ($data as $alias => $details) {
            if ($details['controller'] !== $this->name && !in_array($details['controller'], $done)) {
%>
        <?= $this->Html->link(__('List <%= $this->_pluralHumanName($alias) %>'), ['controller' => '<%= $details['controller'] %>', 'action' => 'index'], ['class' => 'btn btn-default btn-sm', 'title' => 'List <%= $this->_pluralHumanName($alias) %>', 'data-toggle' => 'tooltip', 'escape' => false]) ?> 
        <?= $this->Html->link(__('New <%= Inflector::humanize(Inflector::singularize(Inflector::underscore($alias))) %>'), ['controller' => '<%= $details['controller'] %>', 'action' => 'add'], ['class' => 'btn btn-default btn-sm', 'title' => 'New <%= Inflector::humanize(Inflector::singularize(Inflector::underscore($alias))) %>', 'data-toggle' => 'tooltip', 'escape' => false]) ?> 
<%
                $done[] = $details['controller'];
            }
        }
    }
%>
    </div>
</nav>
<div class="<%= $pluralVar %> view col-lg-10 col-md-9 columns content">
<div class="panel panel-warning">
        <div class="panel-heading">
            <h3 class="panel-title">Details</h3>
        </div>
        <div class="panel-body">
            <table class="vertical-table table table-striped">
        <% if ($groupedFields['string']) : %>
        <% foreach ($groupedFields['string'] as $field) : %>
        <% if (isset($associationFields[$field])) :
                    $details = $associationFields[$field];
        %>
                <tr>
                    <th><?= __('<%= Inflector::humanize($details['property']) %>') ?></th>
                    <td><?= $<%= $singularVar %>->has('<%= $details['property'] %>') ? $this->Html->link($<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %>, ['controller' => '<%= $details['controller'] %>', 'action' => 'view', $<%= $singularVar %>-><%= $details['property'] %>-><%= $details['primaryKey'][0] %>]) : '' ?></td>
                </tr>
        <% else : %>
                <tr>
                    <th><?= __('<%= Inflector::humanize($field) %>') ?></th>
                    <td><?= h($<%= $singularVar %>-><%= $field %>) ?></td>
                </tr>
        <% endif; %>
        <% endforeach; %>
        <% endif; %>
        <% if ($associations['HasOne']) : %>
            <%- foreach ($associations['HasOne'] as $alias => $details) : %>
                <tr>
                    <th><?= __('<%= Inflector::humanize(Inflector::singularize(Inflector::underscore($alias))) %>') ?></th>
                    <td><?= $<%= $singularVar %>->has('<%= $details['property'] %>') ? $this->Html->link($<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %>, ['controller' => '<%= $details['controller'] %>', 'action' => 'view', $<%= $singularVar %>-><%= $details['property'] %>-><%= $details['primaryKey'][0] %>]) : '' ?></td>
                </tr>
            <%- endforeach; %>
        <% endif; %>
        <% if ($groupedFields['number']) : %>
        <% foreach ($groupedFields['number'] as $field) : %>
                <tr>
                    <th><?= __('<%= Inflector::humanize($field) %>') ?></th>
                    <td><?= $this->Number->format($<%= $singularVar %>-><%= $field %>) ?></td>
                </tr>
        <% endforeach; %>
        <% endif; %>
        <% if ($groupedFields['date']) : %>
        <% foreach ($groupedFields['date'] as $field) : %>
                <tr>
                    <th><%= "<%= __('" . Inflector::humanize($field) . "') %>" %></th>
                    <td><?= h($<%= $singularVar %>-><%= $field %>) ?></td>
                </tr>
        <% endforeach; %>
        <% endif; %>
        <% if ($groupedFields['boolean']) : %>
        <% foreach ($groupedFields['boolean'] as $field) : %>
                <tr>
                    <th><?= __('<%= Inflector::humanize($field) %>') ?></th>
                    <td><?= $<%= $singularVar %>-><%= $field %> ? __('Yes') : __('No'); ?></td>
                </tr>
        <% endforeach; %>
        <% endif; %>
            </table>
        <% if ($groupedFields['text']) : %>
        <% foreach ($groupedFields['text'] as $field) : %>
            <div class="row">
                <h4><?= __('<%= Inflector::humanize($field) %>') ?></h4>
                <?= $this->Text->autoParagraph(h($<%= $singularVar %>-><%= $field %>)); ?>
            </div>
        <% endforeach; %>
        <% endif; %>
        <%
        $relations = $associations['HasMany'] + $associations['BelongsToMany'];
        foreach ($relations as $alias => $details):
            $otherSingularVar = Inflector::variable($alias);
            $otherPluralHumanName = Inflector::humanize(Inflector::underscore($details['controller']));
            %>
            <div class="related">
                <h4><?= __('Related <%= $otherPluralHumanName %>') ?></h4>
                <?php if (!empty($<%= $singularVar %>-><%= $details['property'] %>)): ?>
                <table cellpadding="0" cellspacing="0">
                    <tr>
        <% foreach ($details['fields'] as $field): %>
                        <th><?= __('<%= Inflector::humanize($field) %>') ?></th>
        <% endforeach; %>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                    <?php foreach ($<%= $singularVar %>-><%= $details['property'] %> as $<%= $otherSingularVar %>): ?>
                    <tr>
                    <%- foreach ($details['fields'] as $field): %>
                        <td><?= h($<%= $otherSingularVar %>-><%= $field %>) ?></td>
                    <%- endforeach; %>
                    <%- $otherPk = "\${$otherSingularVar}->{$details['primaryKey'][0]}"; %>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['controller' => '<%= $details['controller'] %>', 'action' => 'view', <%= $otherPk %>]) ?>
                            <?= $this->Html->link(__('Edit'), ['controller' => '<%= $details['controller'] %>', 'action' => 'edit', <%= $otherPk %>]) ?>
                            <?= $this->Form->postLink(__('Delete'), ['controller' => '<%= $details['controller'] %>', 'action' => 'delete', <%= $otherPk %>], ['confirm' => __('Are you sure you want to delete # {0}?', <%= $otherPk %>)]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
<% endforeach; %>
</div>
