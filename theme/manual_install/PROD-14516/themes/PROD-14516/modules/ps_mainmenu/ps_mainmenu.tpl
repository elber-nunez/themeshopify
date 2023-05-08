{assign var=_counter value=0}
{function name="menu" nodes=[] depth=0 parent=null}
    {if $nodes|count}
      <ul class="list-unstyled ul-menu m-0{if $depth == 0} d-flex{/if}" {if $depth == 0}id="top-menu"{/if} data-depth="{$depth}">
        {foreach from=$nodes item=node}
          <li class="{$node.type}{if $node.current} current {/if}{if $depth == 0} position-relative d-flex{/if}{if $depth >= 0} dropdown-item{/if}{if $depth === 1} dropdown-submenu{/if}" id="{$node.page_identifier}">
            {assign var=_counter value=$_counter+1}
              <a
                href="{$node.url}" data-depth="{$depth}"
                {if $node.open_in_new_window} target="_blank" {/if}
              >
                {$node.label}
              </a>
              {if $node.children|count}
                {* Cannot use page identifier as we can have the same page several times *}
                {assign var=_expand_id value=10|mt_rand:100000}
                <span class="collapsed" data-target="#top_sub_menu_{$_expand_id}" data-toggle="collapse">
                  <i class="fa fa-angle-down ml-1" aria-hidden="true"></i>
                </span>
                <div {if $depth === 0} class="popover collapse"{else} class="collapse"{/if} id="top_sub_menu_{$_expand_id}">
                  {menu nodes=$node.children depth=$node.depth parent=$node}
                </div>
              {/if}
            </li>
        {/foreach}
      </ul>
    {/if}
{/function}

<div class="menu d-flex justify-content-end">
    {menu nodes=$menu.children}
</div>
