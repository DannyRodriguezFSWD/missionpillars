<div class="gs-links">
    <div class="grid-item ::CLASS::" id="::ID::">
        <style>
        .gs-links-card .alert a {
            display: inline-block;
            min-width: 20ch;
        }
        /* in case we use font-awesome badge, here's some styling */
        .gs-links-card .alert i {
            color: inherit;
        }
        .gs-links-card .alert a i {
            font-size: 2em;
            vertical-align: middle;
            margin-right: 1ch;
        }

        .gs-links-deck-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            grid-gap: .5rem;
        }

        </style>
        <div data-id="::ID::" data-order="::ORDER::">
            <div class="card gs-links-card">
                <div class="card-header">
                    @include('widgets.includes.drag-and-drop-icon') <span>::NAME::</span>
                    @include('widgets.fragments.delete')
                    @include('widgets.fragments.resize')
                </div>
                <div class="card-body">
                    <div class="card-deck gs-links-deck-grid">
                        <div class="card border-0">
                                <a class="card-body text-white bg-info" style="text-decoration: none" target="_blank" href="https://support.continuetogive.com/KnowledgeBase/Details/?id=56">
                                    Selling Tickets For Events
                                </a>
                        </div>
                        <div class="card border-0">
                                <a class="card-body text-white bg-info" style="text-decoration: none" target="_blank" href="https://support.continuetogive.com/KnowledgeBase/Details/?id=53">
                                    Customizable Forms
                                </a>
                        </div>
                        <div class="card border-0">
                                <a class="card-body text-white bg-info" style="text-decoration: none" target="_blank" href="https://support.continuetogive.com/KnowledgeBase/Details/?id=64">
                                    Contribution Statements
                                </a>
                        </div>
                        <div class="card border-0">
                                <a target="_blank"class="card-body text-white bg-info"  href=https://support.continuetogive.com/KnowledgeBase/Details/?id=55"">
                                    Emails / Print
                                </a>
                        </div>
                        <div class="card border-0">
                                <a class="card-body text-white bg-info" style="text-decoration: none" target="_blank" href="https://support.continuetogive.com/KnowledgeBase/Details/?id=67">
                                    SMS / Text Messages
                                </a>
                        </div>
                        <div class="card border-0">
                                <a class="card-body text-white bg-info" style="text-decoration: none" target="_blank" href="https://support.continuetogive.com/KnowledgeBase/Details/?id=54">
                                    Group Signup
                                </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
