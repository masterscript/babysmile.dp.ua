{$form.javascript}
<form{$form.attributes}>{$form.hidden}
	<table border="0" class="maintable">
	    
	    {foreach item=sec key=i from=$form.sections}
	        <tr>
	            <td class="header" colspan="2">
	            <b>{$sec.header}</b></td>
	        </tr>
	              
	        {foreach item=element from=$sec.elements}
	            
	            <!-- elements with alternative layout in external template file-->
	            {if $element.style}
	                {include file="smarty-dynamic-`$element.style`.tpl} 
	                
	            {*
	            NOTE: Another way is to have smarty template code in
	            $element.style. In this case you can do:
	            
	            {if $element.style}
	                {eval var=$element.style}
	            *}   
	            
	            <!-- submit or reset button (don't display on frozen forms) -->
	            {elseif $element.type eq "submit" or $element.type eq "reset"}
	                {if not $form.frozen}
	                <tr>   
	                    <td>&nbsp;</td>
	                    <td align="left">{$element.html}</td>
	                </tr>
	                {/if}
	            
	            <!-- normal elements -->
	            {elseif $element.html || $element.type eq "group"}
	                <tr>
	                    <td align="right" valign="top">	                        
							<label for="{$element.name}">{$element.label}{if $element.required}<font color="red">*</font>{/if}</label>:
						</td>
	                    <td>
	                    {if $element.error}<font color="red">{$element.error}</font><br />{/if}
	                    {if $element.type eq "group"}
	                        {foreach key=gkey item=gitem from=$element.elements}
	                            {$gitem.label}
	                            {$gitem.html}{if $gitem.required}<font color="red">*</font>{/if}                           
	                            {if $element.separator}{cycle values=$element.separator}{/if}
	                        {/foreach}
	                    {else}
	                        {$element.html}
	                    {/if}
	                    <div style="font-size: 80%;">{$element.label_note}</div>
	                    </td>
	                </tr>
	            
	            {/if}
	        {/foreach}   
	    {/foreach}
	</table>
</form> 