@php
    $record = $getRecord();
    $recordId = (string) $record->getKey();
    $parentId = (string) ($record->parent_id ?? '');
    $hasChildren = ($record->children_count ?? 0) > 0 || $record->children()->exists();
@endphp

<div class="flex items-center justify-center">
    @if($hasChildren)
        <button
            type="button"
            aria-expanded="true"
            data-pp-toggle-button
            data-record-id="{{ $recordId }}"
            data-parent-id="{{ $parentId }}"
            class="transition duration-200 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-custom-500/10 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
            title="Zwiń/rozwiń pozycje podrzędne"
        >
            <span class="text-lg font-bold block leading-none pp-toggle-icon pp-toggle-open">▾</span>
            <span class="text-lg font-bold block leading-none pp-toggle-icon pp-toggle-closed">▸</span>
            <span class="sr-only">Zwiń/rozwiń</span>
        </button>
    @else
        <span
            data-pp-leaf-marker
            data-record-id="{{ $recordId }}"
            data-parent-id="{{ $parentId }}"
            class="inline-block w-6"
        ></span>
    @endif
</div>

@once
    <style>
        tr[data-collapsed='true'] .pp-toggle-open {
            display: none !important;
        }

        tr[data-collapsed='true'] .pp-toggle-closed {
            display: inline-block !important;
        }

        tr[data-collapsed='false'] .pp-toggle-open {
            display: inline-block !important;
        }

        tr[data-collapsed='false'] .pp-toggle-closed {
            display: none !important;
        }

        tr.pp-hidden-row {
            display: none !important;
        }
    </style>

    <script>
        (function(){
            if (window.__ppProgramPointInitDefined) return;
            window.__ppProgramPointInitDefined = true;
            
            // Global state store for collapsed rows to persist across Livewire updates
            window.__ppCollapsedState = window.__ppCollapsedState || {};

            function escapeSelector(value) {
                if (window.CSS && typeof window.CSS.escape === 'function') {
                    return window.CSS.escape(String(value));
                }
                return String(value).replace(/'/g, "\\'");
            }

            function setHiddenRecursive(parentId, hidden) {
                var selector = "tr[data-parent-id='" + escapeSelector(parentId) + "']";
                var rows = document.querySelectorAll(selector);
                
                rows.forEach(function(row) {
                    var recordId = row.dataset.recordId;
                    
                    if (hidden) {
                        row.classList.add('hidden');
                        row.style.display = 'none';
                        // Also hide children recursively
                        if (recordId) {
                            setHiddenRecursive(recordId, true);
                        }
                    } else {
                        // Showing
                        row.classList.remove('hidden');
                        row.style.display = '';
                        
                        // Check if this row is collapsed itself
                        var isCollapsed = row.dataset.collapsed === 'true';
                        
                        // If this row is a parent (has recordId), handle its children
                        if (recordId) {
                            if (isCollapsed) {
                                // If this row is collapsed, ensure its children remain hidden
                                setHiddenRecursive(recordId, true);
                            } else {
                                // If this row is expanded, show its children
                                setHiddenRecursive(recordId, false);
                            }
                        }
                    }
                });
            }

            function initRows() {
                var markers = document.querySelectorAll('[data-pp-toggle-button], [data-pp-leaf-marker]');
                
                markers.forEach(function(marker) {
                    var row = marker.closest('tr');
                    if (!row) return;

                    var recordId = marker.dataset.recordId || row.dataset.recordId;
                    var parentId = marker.dataset.parentId || row.dataset.parentId || '';
                    
                    // Restore state from global store or default to false (expanded)
                    var isCollapsed = false;
                    if (window.__ppCollapsedState.hasOwnProperty(recordId)) {
                        isCollapsed = window.__ppCollapsedState[recordId];
                    } else {
                        // Fallback to DOM state if present (unlikely on fresh render) or default
                        isCollapsed = row.dataset.collapsed === 'true'; 
                    }

                    row.dataset.recordId = recordId;
                    row.dataset.parentId = parentId;
                    row.dataset.collapsed = isCollapsed ? 'true' : 'false';

                    if (marker.hasAttribute('data-pp-toggle-button')) {
                        marker.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                    }
                    
                    if (parentId) {
                        row.classList.add('pp-child-row');
                    } else {
                        row.classList.add('pp-parent-row');
                    }
                });
                
                // Second pass: enforce visibility based on restored states
                var collapsedParents = document.querySelectorAll('tr[data-collapsed="true"]');
                collapsedParents.forEach(function(parentRow) {
                    var parentId = parentRow.dataset.recordId;
                    if (parentId) {
                        setHiddenRecursive(parentId, true);
                    }
                });
            }

            function handleToggle(button) {
                var row = button.closest('tr');
                if (!row) return;

                var recordId = row.dataset.recordId;
                if (!recordId) return;

                var collapsed = row.dataset.collapsed === 'true';
                collapsed = !collapsed; // Toggle

                // Update DOM
                row.dataset.collapsed = collapsed ? 'true' : 'false';
                button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                
                // Save state
                window.__ppCollapsedState[recordId] = collapsed;

                setHiddenRecursive(recordId, collapsed);
            }

            // Event delegation
            document.addEventListener('click', function(event) {
                var button = event.target.closest('[data-pp-toggle-button]');
                if (!button) return;
                
                event.preventDefault();
                event.stopPropagation();
                handleToggle(button);
            }, true);

            // Initialization hooks
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initRows);
            } else {
                initRows();
            }

            if (window.Livewire && typeof window.Livewire.hook === 'function') {
                window.Livewire.hook('message.processed', function() {
                    initRows();
                });
            }
            
            // Expose for debugging
            window.__ppToggleInit = initRows;
        })();
    </script>
@endonce
