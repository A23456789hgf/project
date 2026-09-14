                 <div class="content-card-elegant suggestions-card-elegant">
                    <div class="card-header-elegant">
                        <div class="header-title-elegant">
                            <div class="title-dot-gold"></div>
                            <h5 class="mb-0 fw-semibold">صندوق المقترحات</h5>
                        </div>
                        <button class="btn-icon-elegant btn-navy-elegant" onclick="loadSuggestions()" title="تحديث">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>

                    <!-- Suggestions List -->
                    <div class="list-container-elegant suggestions-list-elegant" id="suggestionsContainer">
                        <div id="suggestionsList" class="p-3">
                            <div class="text-center py-4">
                                <div class="spinner-elegant">
                                    <div class="spinner-item"></div>
                                    <div class="spinner-item"></div>
                                    <div class="spinner-item"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Form -->
                    @can('suggestions.create')
                        <div class="suggestion-input-elegant">
                            <form id="suggestionForm" class="suggestion-form-elegant">
                                @csrf
                                <div class="input-wrapper-elegant">
                                    <textarea name="content" id="suggestionContent" class="form-control-elegant" rows="1"
                                        placeholder="اكتب مقترحك هنا بكل ثقة..." style="resize: none;"></textarea>
                                    <button type="submit" class="btn-send-elegant">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endcan
                </div>
