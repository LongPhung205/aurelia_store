@extends('client.profile.layout')

@section('profile_content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8" x-data="addressApp()" x-init="initApp()">
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Sổ Địa Chỉ</h2>
            <p class="text-sm text-gray-500 mt-1">Quản lý các địa chỉ nhận hàng của bạn</p>
        </div>
        <button @click="openModal()" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#C2185B] transition-colors flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> Thêm địa chỉ mới
        </button>
    </div>

    <!-- Danh sách địa chỉ -->
    <div class="space-y-4">
        @forelse($addresses as $address)
            <div class="border {{ $address->is_default ? 'border-brand bg-brand/5' : 'border-gray-200' }} rounded-xl p-5 flex flex-col sm:flex-row justify-between gap-4 transition-colors">
                <div class="flex-grow">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-bold text-gray-900 text-lg">{{ $address->name }}</span>
                        <span class="text-gray-400">|</span>
                        <span class="text-gray-600">{{ $address->phone }}</span>
                    </div>
                    <div class="text-gray-600 text-sm mb-1">
                        {{ $address->address }}
                    </div>
                    <!-- Địa chỉ GHN text có thể lưu lại dạng tên hoặc fetch lại. Ở đây vì đơn giản ta in raw district/ward -->
                    <!-- Trong thực tế nên lưu string Tên Tỉnh, Tên Huyện. Để nhanh, ta cứ hiển thị chi tiết address. -->
                    @if($address->is_default)
                        <div class="mt-2 inline-block px-2 py-1 bg-red-100 text-brand text-xs font-medium rounded border border-brand/20">
                            Mặc định
                        </div>
                    @endif
                </div>
                <div class="flex flex-col items-end justify-between gap-2 shrink-0">
                    <div class="flex items-center gap-3">
                        <button @click="editAddress({{ json_encode($address) }})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Cập nhật</button>
                        @if(!$address->is_default)
                            <form action="{{ route('profile.addresses.destroy', $address->id) }}" method="POST" class="inline form-delete"
                                  data-confirm-title="Xóa địa chỉ nhận hàng?"
                                  data-confirm-text="Bạn có chắc chắn muốn xóa địa chỉ này khỏi sổ địa chỉ không?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium cursor-pointer">Xóa</button>
                            </form>
                        @endif
                    </div>
                    @if(!$address->is_default)
                        <form action="{{ route('profile.addresses.default', $address->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="border border-gray-300 text-gray-700 hover:border-brand hover:text-brand px-3 py-1.5 rounded-lg text-sm transition-colors bg-white shadow-sm">
                                Thiết lập mặc định
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10">
                <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">Chưa có địa chỉ nào</h3>
                <p class="text-gray-500 mb-4">Bạn chưa thiết lập địa chỉ nhận hàng. Hãy thêm ngay!</p>
                <button @click="openModal()" class="text-brand font-medium hover:underline">Thêm địa chỉ mới</button>
            </div>
        @endforelse
    </div>

    <!-- Modal Thêm/Sửa Địa chỉ -->
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="isModalOpen" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form :action="formAction" method="POST">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title" x-text="isEditing ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ mới'"></h3>
                            <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                                <i class="bi bi-x-lg text-xl"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                                <input type="text" name="name" x-model="form.name" required class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                                <input type="text" name="phone" x-model="form.phone" required class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh / Thành phố</label>
                                <select name="province_id" x-model="selectedProvince" @change="fetchDistricts" required class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50">
                                    <option value="">Chọn Tỉnh/Thành</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province['ProvinceID'] }}">{{ $province['ProvinceName'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quận / Huyện</label>
                                <select name="district_id" x-model="selectedDistrict" @change="fetchWards" required :disabled="!selectedProvince || isLoadingDistricts" class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 disabled:bg-gray-100">
                                    <option value="">Chọn Quận/Huyện</option>
                                    <template x-for="d in districts" :key="d.DistrictID">
                                        <option :value="d.DistrictID" x-text="d.DistrictName"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phường / Xã</label>
                                <select name="ward_code" x-model="selectedWard" required :disabled="!selectedDistrict || isLoadingWards" class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 disabled:bg-gray-100">
                                    <option value="">Chọn Phường/Xã</option>
                                    <template x-for="w in wards" :key="w.WardCode">
                                        <option :value="w.WardCode" x-text="w.WardName"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ cụ thể</label>
                            <input type="text" name="address" x-model="form.address" placeholder="Số nhà, tên đường..." required class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50">
                        </div>

                        <div class="flex items-center" x-show="!form.is_default">
                            <input type="checkbox" name="is_default" id="is_default" value="1" x-model="form.set_default" class="rounded border-gray-300 text-brand focus:ring-brand w-4 h-4">
                            <label for="is_default" class="ml-2 text-sm text-gray-700">Đặt làm địa chỉ mặc định</label>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-brand text-base font-medium text-white hover:bg-[#C2185B] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Hoàn thành
                        </button>
                        <button type="button" @click="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Trở lại
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function addressApp() {
        return {
            isModalOpen: false,
            isEditing: false,
            formAction: '',
            
            districts: [],
            wards: [],
            
            selectedProvince: '',
            selectedDistrict: '',
            selectedWard: '',
            
            isLoadingDistricts: false,
            isLoadingWards: false,
            
            form: {
                id: null,
                name: '',
                phone: '',
                address: '',
                is_default: false,
                set_default: false
            },
            
            initApp() {
                // Initialize if needed
            },
            
            openModal() {
                this.isEditing = false;
                this.formAction = '{{ route('profile.addresses.store') }}';
                this.resetForm();
                this.isModalOpen = true;
            },
            
            closeModal() {
                this.isModalOpen = false;
            },
            
            editAddress(address) {
                this.isEditing = true;
                this.formAction = `/profile/addresses/${address.id}`;
                
                this.form.id = address.id;
                this.form.name = address.name;
                this.form.phone = address.phone;
                this.form.address = address.address;
                this.form.is_default = address.is_default;
                this.form.set_default = false;
                
                this.selectedProvince = address.province_id;
                
                // Fetch districts then set selected district
                this.fetchDistricts().then(() => {
                    this.selectedDistrict = address.district_id;
                    return this.fetchWards();
                }).then(() => {
                    this.selectedWard = address.ward_code;
                });
                
                this.isModalOpen = true;
            },
            
            resetForm() {
                this.form = { id: null, name: '', phone: '', address: '', is_default: false, set_default: false };
                this.selectedProvince = '';
                this.selectedDistrict = '';
                this.selectedWard = '';
                this.districts = [];
                this.wards = [];
            },
            
            fetchDistricts() {
                if(!this.isEditing) {
                    this.selectedDistrict = '';
                    this.selectedWard = '';
                }
                this.districts = [];
                this.wards = [];
                
                if (!this.selectedProvince) return Promise.resolve();
                
                this.isLoadingDistricts = true;
                return fetch(`{{ route('checkout.get_districts') }}?province_id=${this.selectedProvince}`)
                    .then(res => res.json())
                    .then(data => {
                        this.districts = data;
                        this.isLoadingDistricts = false;
                    })
                    .catch(() => {
                        this.isLoadingDistricts = false;
                    });
            },
            
            fetchWards() {
                if(!this.isEditing) {
                    this.selectedWard = '';
                }
                this.wards = [];
                
                if (!this.selectedDistrict) return Promise.resolve();
                
                this.isLoadingWards = true;
                return fetch(`{{ route('checkout.get_wards') }}?district_id=${this.selectedDistrict}`)
                    .then(res => res.json())
                    .then(data => {
                        this.wards = data;
                        this.isLoadingWards = false;
                    })
                    .catch(() => {
                        this.isLoadingWards = false;
                    });
            }
        }
    }
</script>
@endsection
