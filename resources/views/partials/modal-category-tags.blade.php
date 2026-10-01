<!-- ================= MODAL: CHỌN THỂ LOẠI & THẺ TAG CHI TIẾT ================= -->
<div id="modal-category-tags" class="fixed inset-0 z-[9999] bg-slate-950/70 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-5" style="z-index: 9999 !important;">
    <div class="bg-white rounded-3xl max-w-4xl w-full p-5 sm:p-6 shadow-2xl border border-slate-200 relative max-h-[92vh] flex flex-col animate-in fade-in zoom-in duration-150" style="z-index: 10000 !important;">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i data-lucide="tags" class="w-4 h-4 text-sky-600"></i>
                    <span>Chọn Thể Loại & Nhãn Phân Loại Sách</span>
                </h3>
                <p class="text-xs text-slate-500">Đánh dấu các thẻ thuộc tính cho tác phẩm để hệ thống lọc và tìm kiếm chính xác</p>
            </div>
            <button type="button" onclick="closeModal('modal-category-tags')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Filter / Search Quick Tag -->
        <div class="flex flex-wrap items-center gap-2 pt-3 pb-2 border-b border-slate-100">
            <span class="text-xs font-bold text-slate-700">Tìm kiếm tag:</span>
            <div class="relative flex-1 max-w-sm">
                <input type="text" id="tag-filter-input" oninput="filterTags(this.value)" placeholder="Nhập gợi ý để tìm tag (VD: Tiên hiệp, Đô thị, Xuyên việt...)"
                    class="w-full py-1.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
            </div>
            <button type="button" onclick="clearAllSelectedTags()" class="text-[11px] text-slate-500 hover:text-red-600 px-2.5 py-1 rounded-lg hover:bg-red-50 border border-slate-200 transition">
                Xóa chọn tất cả
            </button>
            <div class="ml-auto text-xs text-sky-700 font-semibold bg-sky-50 px-3 py-1 rounded-xl border border-sky-100">
                Đã chọn: <span id="tags-count-badge" class="font-bold">0</span> tag
            </div>
        </div>

        <!-- Scrollable Tag Selection Body -->
        <div class="overflow-y-auto pr-1.5 space-y-4 py-3 flex-1 text-xs select-none" id="tag-groups-container">
            <!-- Nhóm 1: Tính chất -->
            <div class="tag-group border-b border-sky-100/70 pb-3">
                <div class="font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    <span>Tính chất:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 pl-3">
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Nguyên sang" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Nguyên sang</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Diễn sinh" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Diễn sinh</span>
                    </label>
                </div>
            </div>

            <!-- Nhóm 2: Giới tính / Xu hướng đọc -->
            <div class="tag-group border-b border-sky-100/70 pb-3">
                <div class="font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                    <span>Giới tính / Hướng đối tượng:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 pl-3">
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Ngôn tình" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Ngôn tình</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Nam sinh" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Nam sinh</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Đam mỹ" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Đam mỹ</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Bách hợp" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Bách hợp</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Nữ tôn" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Nữ tôn</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Không CP" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Không CP</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Đa nguyên" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Đa nguyên</span>
                    </label>
                </div>
            </div>

            <!-- Nhóm 3: Thời đại -->
            <div class="tag-group border-b border-sky-100/70 pb-3">
                <div class="font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Thời đại:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 pl-3">
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Cổ đại" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Cổ đại</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Cận đại" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Cận đại</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Hiện đại" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Hiện đại</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Tương lai" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Tương lai</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Giả tưởng lịch sử" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Giả tưởng lịch sử</span>
                    </label>
                </div>
            </div>

            <!-- Nhóm 4: Kết thúc -->
            <div class="tag-group border-b border-sky-100/70 pb-3">
                <div class="font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Kết thúc:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 pl-3">
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="HE (Hạnh phúc)" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>HE (Happy Ending)</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="SE (Bi kịch)" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>SE (Sad Ending)</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="OE (Mở)" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>OE (Open Ending)</span>
                    </label>
                </div>
            </div>

            <!-- Nhóm 5: Loại hình / Thể loại chính -->
            <div class="tag-group border-b border-sky-100/70 pb-3">
                <div class="font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    <span>Loại hình / Thể loại chính:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 pl-3">
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Tình cảm" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Tình cảm</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Làm sự nghiệp" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Làm sự nghiệp</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Tiên hiệp" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Tiên hiệp</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Huyền huyễn" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Huyền huyễn</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Khoa học viễn tưởng" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Khoa học viễn tưởng</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Mạt thế" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Mạt thế</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Sinh tồn" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Sinh tồn</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Tranh bá" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Tranh bá</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Võ hiệp" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Võ hiệp</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Trinh thám" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Trinh thám</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Kinh dị" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Kinh dị</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Quan trường" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Quan trường</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Kinh thương" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Kinh thương</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Quân sự" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Quân sự</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Xây dựng" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Xây dựng</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Làm ruộng" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Làm ruộng</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Huyền học" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Huyền học</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Phim ảnh" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Phim ảnh</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Manga anime" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Manga anime</span>
                    </label>
                </div>
            </div>

            <!-- Nhóm 6: Thế giới & Không gian -->
            <div class="tag-group border-b border-sky-100/70 pb-3">
                <div class="font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                    <span>Thế giới & Không gian:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 pl-3">
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Hồng hoang" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Hồng hoang</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Dị thế" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Dị thế</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Thú nhân" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Thú nhân</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Tinh tế" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Tinh tế</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Thế giới song song" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Thế giới song song</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Nguyên thủy" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Nguyên thủy</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Cyberpunk" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Cyberpunk</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Steampunk" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Steampunk</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Phế thổ" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Phế thổ</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Trò chơi xâm lấn" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Trò chơi xâm lấn</span>
                    </label>
                </div>
            </div>

            <!-- Nhóm 7: Thời không / Xuyên việt -->
            <div class="tag-group border-b border-sky-100/70 pb-3">
                <div class="font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span>Thời không / Xuyên việt:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 pl-3">
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Xuyên việt" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Xuyên việt</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Trọng sinh" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Trọng sinh</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Xuyên thư" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Xuyên thư</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Xuyên nhanh" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Xuyên nhanh</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Xuyên game" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Xuyên game</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Cổ xuyên kim" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Cổ xuyên kim</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Chết đi sống lại" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Chết đi sống lại</span>
                    </label>
                </div>
            </div>

            <!-- Nhóm 8: Bàn tay vàng & Hệ thống -->
            <div class="tag-group border-b border-sky-100/70 pb-3">
                <div class="font-bold text-slate-900 mb-2 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                    <span>Bàn tay vàng / Thiết lập nhân vật:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 pl-3">
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Hệ thống" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Hệ thống</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Vô bàn tay vàng" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Vô bàn tay vàng</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Tùy thân không gian" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Tùy thân không gian</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Đọc tâm" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Đọc tâm</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Đánh dấu" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Đánh dấu</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Dưỡng thành" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Dưỡng thành</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="1v1" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>1v1</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Thanh mai trúc mã" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Thanh mai trúc mã</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Cường cường" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Cường cường</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Hài hước" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Hài hước</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Chữa lành" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Chữa lành</span>
                    </label>
                    <label class="tag-item flex items-center gap-2 cursor-pointer hover:text-sky-700">
                        <input type="checkbox" value="Sảng văn" onchange="onTagChecked(this)" class="tag-checkbox rounded text-sky-600 border-slate-300">
                        <span>Sảng văn</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
            <div class="text-xs text-slate-500">
                Gợi ý: Thể loại chính sẽ được gán từ thẻ đầu tiên bạn chọn.
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeModal('modal-category-tags')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Hủy bỏ
                </button>
                <button type="button" onclick="confirmCategoryTagSelection()" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Áp Dụng Thẻ Tag Đã Chọn</span>
                </button>
            </div>
        </div>
    </div>
</div>