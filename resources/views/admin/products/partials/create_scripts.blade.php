<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
            const modalEl = document.getElementById('createProductModal');
            if (modalEl) {
                const modal = new Modal(modalEl);
                modal.show();
            }
        @endif
    });

    document.getElementById('btn-select-all-colors').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.color-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    });

    document.getElementById('btn-select-all-sizes').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.size-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    });

    document.getElementById('btn-generate-variants').addEventListener('click', function() {
        const selectedColors = Array.from(document.querySelectorAll('.color-checkbox:checked')).map(cb => ({ id: cb.value, name: cb.dataset.name }));
        const selectedSizes = Array.from(document.querySelectorAll('.size-checkbox:checked')).map(cb => ({ id: cb.value, name: cb.dataset.name }));
        
        const tbody = document.getElementById('variants-tbody');
        const table = document.getElementById('variants-table');
        const msg = document.getElementById('no-variant-msg');
        const colorImagesSection = document.getElementById('color-images-section');
        const colorImagesContainer = document.getElementById('color-images-container');
        const bulkSetupSection = document.getElementById('bulk-setup-section');
        
        if (selectedColors.length === 0 && selectedSizes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Thiếu thông tin',
                text: 'Vui lòng chọn ít nhất 1 màu sắc hoặc 1 kích cỡ để tạo biến thể tổ hợp.',
                confirmButtonText: 'Đã hiểu'
            });
            return;
        }

        // Bỏ tbody cũ và gen lại
        tbody.innerHTML = '';
        colorImagesContainer.innerHTML = '';
        
        table.classList.remove('hidden');
        msg.classList.add('hidden');
        bulkSetupSection.classList.remove('hidden');
        
        // Render Ảnh theo màu sắc
        if (selectedColors.length > 0) {
            colorImagesSection.classList.remove('hidden');
            selectedColors.forEach(color => {
                const col = document.createElement('div');
                col.className = 'md:col-span-1 flex flex-col justify-center items-center text-center relative';
                
                col.innerHTML = `
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm w-full p-2">
                        <label class="block text-xs font-semibold text-gray-900 truncate w-full mb-1">${color.name}</label>
                        <input type="file" name="color_images[${color.id}]" class="block w-full text-xs text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none color-image-input" accept="image/*" data-color-id="${color.id}">
                        <div class="mt-2 text-gray-500 text-xs img-preview-area flex items-center justify-center bg-gray-50 rounded" id="preview-color-${color.id}" style="min-height: 80px;">
                            <span>Chưa có ảnh</span>
                        </div>
                    </div>
                `;
                colorImagesContainer.appendChild(col);
            });

            // Preview ảnh khi chọn
            document.querySelectorAll('.color-image-input').forEach(input => {
                input.addEventListener('change', function() {
                    const colorId = this.dataset.colorId;
                    const previewArea = document.getElementById(`preview-color-${colorId}`);
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewArea.innerHTML = `<img src="${e.target.result}" style="max-width:100%; max-height:80px; object-fit:cover; border-radius:4px;">`;
                        }
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        previewArea.innerHTML = `<span>Chưa có ảnh</span>`;
                    }
                });
            });
        } else {
            colorImagesSection.classList.add('hidden');
            colorImagesContainer.innerHTML = '';
        }

        // Xử lý tạo variants table
        let variants = [];
        if (selectedColors.length > 0 && selectedSizes.length > 0) {
            selectedColors.forEach(c => {
                selectedSizes.forEach(s => {
                    variants.push({ color: c, size: s });
                });
            });
        } else if (selectedColors.length > 0) {
            selectedColors.forEach(c => variants.push({ color: c, size: null }));
        } else {
            selectedSizes.forEach(s => variants.push({ color: null, size: s }));
        }

        variants.forEach((v, index) => {
            const tr = document.createElement('tr');
            
            const colorName = v.color ? v.color.name : '-';
            const sizeName = v.size ? v.size.name : '-';
            // Index array [color_id_size_id] cho Laravel request
            const arrayKey = (v.color ? v.color.id : '0') + '_' + (v.size ? v.size.id : '0');

            tr.className = 'bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600';
            tr.innerHTML = `
                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    ${colorName}
                    ${v.color ? `<input type="hidden" name="variants[${arrayKey}][color_id]" value="${v.color.id}">` : ''}
                </td>
                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    <span class="bg-gray-800 text-white text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">${sizeName}</span>
                    ${v.size ? `<input type="hidden" name="variants[${arrayKey}][size_id]" value="${v.size.id}">` : ''}
                </td>
                <td class="px-6 py-4">
                    <div class="relative w-full">
                        <input type="number" name="variants[${arrayKey}][price]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 variant-price pr-8" required min="0" placeholder="0">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">đ</div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <input type="text" name="variants[${arrayKey}][sku]" class="bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Hệ thống tự tạo" readonly>
                </td>
                <td class="px-6 py-4 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 hover:bg-red-100 rounded p-1 btn-remove-variant"><i class="bi bi-x-lg"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Xóa dòng variant
        document.querySelectorAll('.btn-remove-variant').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('tr').remove();
                if (tbody.children.length === 0) {
                    table.classList.add('hidden');
                    bulkSetupSection.classList.add('hidden');
                    msg.classList.remove('hidden');
                    colorImagesSection.classList.add('hidden');
                }
            });
        });
    });

    // Clear all variants
    document.getElementById('btn-clear-all-variants').addEventListener('click', function() {
        Swal.fire({
            title: 'Xóa tất cả biến thể?',
            text: "Bạn có chắc chắn muốn xóa toàn bộ biến thể đã tạo không? Hành động này không thể hoàn tác.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa tất cả',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('variants-tbody').innerHTML = '';
                document.getElementById('variants-table').classList.add('d-none');
                document.getElementById('bulk-setup-section').classList.add('d-none');
                document.getElementById('color-images-section').classList.add('d-none');
                document.getElementById('no-variant-msg').classList.remove('d-none');
            }
        });
    });

    // Tính năng Bulk Edit (Điền hàng loạt)
    document.getElementById('btn-apply-bulk').addEventListener('click', function() {
        const bulkPrice = document.getElementById('bulk-price').value;

        if (bulkPrice) {
            document.querySelectorAll('.variant-price').forEach(input => input.value = bulkPrice);
        }
    });

    // Handle material selection (Select or Custom)
    const materialSelect = document.getElementById('material-select');
    const materialInput = document.getElementById('material-input');
    const materialHidden = document.getElementById('material-hidden');

    materialSelect.addEventListener('change', function() {
        if(this.value === '_other_') {
            materialInput.classList.remove('d-none');
            materialInput.focus();
            materialHidden.value = materialInput.value;
        } else {
            materialInput.classList.add('d-none');
            materialHidden.value = this.value;
        }
    });

    materialInput.addEventListener('input', function() {
        materialHidden.value = this.value;
    });
</script>
