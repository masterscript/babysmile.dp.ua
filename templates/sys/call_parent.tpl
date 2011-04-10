{assign var='parent_caller' value=$page->getParentCaller($params)}
{if $parent_caller}
	{assign var='page_tmp' value=$page} {* вот из за этого не будут работать рекурсивные вызовы... *}
	{assign var='page' value=$parent_caller.obj}
	{include file=$parent_caller.params.file params=$parent_caller.params}
	{assign var='page' value=$page_tmp}
{/if}