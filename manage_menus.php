<?php
require_once 'db_config.php';
require_once 'auth.php';

// Only Admin can access
if ($user_role_id != 1) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการเมนู - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js for simple state management -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SortableJS for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .ghost-sortable { opacity: 0.4; background-color: #f3f4f6; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <?php include 'navbar.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="menuManager()">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-list-ul text-blue-600 mr-2"></i> จัดการเมนูระบบ
            </h1>
            <button @click="openModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center gap-2">
                <i class="fas fa-plus"></i> เพิ่มเมนูใหม่
            </button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button @click="currentTab = 'backend'; fetchMenus()" :class="currentTab === 'backend' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-lg flex items-center gap-2">
                    <i class="fas fa-cogs"></i> เมนูเจ้าหน้าที่ (Backend)
                </button>
                <button @click="currentTab = 'frontend'; fetchMenus()" :class="currentTab === 'frontend' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-lg flex items-center gap-2">
                    <i class="fas fa-globe"></i> เมนูประชาชน (Frontend)
                </button>
            </nav>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center text-sm text-gray-500">
                <span><i class="fas fa-info-circle mr-1"></i> สามารถลากและวาง (Drag & Drop) เพื่อจัดเรียงลำดับเมนูได้</span>
                <button @click="saveOrder()" x-show="orderChanged" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-bold transition">
                    <i class="fas fa-save mr-1"></i> บันทึกลำดับ
                </button>
            </div>
            
            <div class="p-0">
                <!-- Loading State -->
                <div x-show="loading" class="p-8 text-center text-gray-400">
                    <i class="fas fa-circle-notch fa-spin fa-2x mb-2"></i>
                    <p>กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Menu List -->
                <ul id="menu-list" class="divide-y divide-gray-100" x-show="!loading">
                    <template x-for="menu in filteredMenus" :key="menu.id">
                        <!-- Only render parent items at root level -->
                        <template x-if="!menu.parent_id">
                            <li class="bg-white" :data-id="menu.id">
                                <div class="px-6 py-4 flex items-center justify-between group">
                                    <div class="flex items-center gap-4 flex-1">
                                        <i class="fas fa-grip-vertical text-gray-300 cursor-move hover:text-gray-500 p-2"></i>
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                            <i :class="menu.icon || 'fas fa-link'"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800" x-text="menu.title"></h3>
                                            <div class="flex items-center gap-3 text-xs mt-1">
                                                <span class="text-gray-500"><i class="fas fa-link mr-1"></i> <span x-text="menu.url"></span></span>
                                                <template x-if="menu.allowed_roles">
                                                    <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded border border-purple-200">
                                                        <i class="fas fa-user-shield mr-1"></i> จำกัดสิทธิ์
                                                    </span>
                                                </template>
                                                <template x-if="!menu.is_active">
                                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded">ปิดใช้งาน</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openModal('add', menu.id)" class="text-blue-500 hover:text-blue-700 p-2 text-sm" title="เพิ่มเมนูย่อย" x-show="currentTab === 'backend'">
                                            <i class="fas fa-plus"></i> เพิ่มเมนูย่อย
                                        </button>
                                        <button @click="openModal('edit', menu.id)" class="text-orange-500 hover:text-orange-700 p-2" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteMenu(menu.id)" class="text-red-500 hover:text-red-700 p-2" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Child Menus -->
                                <ul class="child-sortable pl-14 pr-6 pb-2 space-y-1" :data-parent-id="menu.id">
                                    <template x-for="child in getChildren(menu.id)" :key="child.id">
                                        <li class="bg-gray-50 border border-gray-200 rounded-md p-3 flex items-center justify-between group" :data-id="child.id">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-grip-vertical text-gray-300 cursor-move hover:text-gray-500"></i>
                                                <i :class="child.icon || 'fas fa-angle-right'" class="text-gray-400 w-5 text-center"></i>
                                                <span class="font-medium text-gray-700" x-text="child.title"></span>
                                                <span class="text-xs text-gray-400" x-text="child.url"></span>
                                                <template x-if="child.allowed_roles">
                                                    <i class="fas fa-lock text-purple-400 text-xs" title="จำกัดสิทธิ์"></i>
                                                </template>
                                                <template x-if="!child.is_active">
                                                    <span class="bg-red-100 text-red-700 px-1 py-0.5 text-xs rounded">ปิดใช้งาน</span>
                                                </template>
                                            </div>
                                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="openModal('edit', child.id)" class="text-orange-500 hover:text-orange-700" title="แก้ไข">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="deleteMenu(child.id)" class="text-red-500 hover:text-red-700" title="ลบ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </li>
                        </template>
                    </template>
                    <li x-show="filteredMenus.length === 0" class="px-6 py-8 text-center text-gray-500">
                        ไม่พบข้อมูลเมนู
                    </li>
                </ul>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="isModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="isModalOpen" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitForm">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title" x-text="modalMode === 'add' ? 'เพิ่มเมนูใหม่' : 'แก้ไขเมนู'"></h3>
                                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <!-- Type -->
                                <input type="hidden" name="action" x-model="modalMode">
                                <input type="hidden" name="id" x-model="formData.id">
                                <input type="hidden" name="menu_type" x-model="currentTab">
                                
                                <div x-show="currentTab === 'backend'">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">เมนูหลัก (Parent)</label>
                                    <select name="parent_id" x-model="formData.parent_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- ไม่มี (เป็นเมนูหลัก) --</option>
                                        <template x-for="m in backendParentMenus" :key="m.id">
                                            <option :value="m.id" x-text="m.title" x-show="m.id != formData.id"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อเมนู <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" x-model="formData.title" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ลิงก์ URL</label>
                                    <input type="text" name="url" x-model="formData.url" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="เช่น dashboard.php หรือ #">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ไอคอน (FontAwesome Class)</label>
                                    <input type="text" name="icon" x-model="formData.icon" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="เช่น fas fa-home">
                                    <p class="text-xs text-gray-500 mt-1">อ้างอิงจาก <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" class="text-blue-500 hover:underline">FontAwesome Free</a></p>
                                </div>

                                <div x-show="currentTab === 'frontend'">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CSS Class พิเศษ (สำหรับทำปุ่มสีต่างๆ)</label>
                                    <input type="text" name="css_class" x-model="formData.css_class" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="เช่น bg-red-600 text-white ...">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">จำกัดสิทธิ์ผู้ใช้ (Roles)</label>
                                    <div class="bg-gray-50 p-3 rounded-md border border-gray-200 space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" :checked="isAllRoles()" @change="toggleAllRoles($event)" class="rounded text-blue-600 mr-2">
                                            <span class="text-sm">ดูได้ทุกคน (ไม่ต้องจำกัดสิทธิ์)</span>
                                        </label>
                                        <hr class="border-gray-200">
                                        <div class="grid grid-cols-2 gap-2 mt-2">
                                            <!-- Roles are hardcoded based on DB for simplicity, or fetched. I'll hardcode here -->
                                            <label class="flex items-center">
                                                <input type="checkbox" name="allowed_roles[]" value="1" x-model="formData.allowed_roles" class="rounded text-blue-600 mr-2">
                                                <span class="text-sm">Admin</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="allowed_roles[]" value="2" x-model="formData.allowed_roles" class="rounded text-blue-600 mr-2">
                                                <span class="text-sm">Governor</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="allowed_roles[]" value="3" x-model="formData.allowed_roles" class="rounded text-blue-600 mr-2">
                                                <span class="text-sm">District Chief</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="allowed_roles[]" value="4" x-model="formData.allowed_roles" class="rounded text-blue-600 mr-2">
                                                <span class="text-sm">Officer</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="modalMode === 'edit'">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" x-model="formData.is_active" class="rounded text-blue-600 mr-2 h-4 w-4">
                                        <span class="text-sm font-medium text-gray-700">เปิดใช้งาน (Active)</span>
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition">
                                บันทึกข้อมูล
                            </button>
                            <button type="button" @click="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                                ยกเลิก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('menuManager', () => ({
                menus: [],
                loading: true,
                currentTab: 'backend',
                isModalOpen: false,
                modalMode: 'add',
                orderChanged: false,
                sortables: [],
                formData: {
                    id: '',
                    parent_id: '',
                    title: '',
                    url: '#',
                    icon: '',
                    css_class: '',
                    allowed_roles: [],
                    is_active: 1
                },

                init() {
                    this.fetchMenus();
                },

                get filteredMenus() {
                    return this.menus.filter(m => m.menu_type === this.currentTab);
                },

                get backendParentMenus() {
                    return this.menus.filter(m => m.menu_type === 'backend' && !m.parent_id);
                },

                getChildren(parentId) {
                    return this.menus.filter(m => m.parent_id == parentId);
                },

                fetchMenus() {
                    this.loading = true;
                    fetch('api_menus.php')
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.menus = data.data.map(m => {
                                    if(m.allowed_roles) {
                                        m.allowed_roles = JSON.parse(m.allowed_roles).map(String);
                                    } else {
                                        m.allowed_roles = [];
                                    }
                                    return m;
                                });
                                this.initSortable();
                            }
                            this.loading = false;
                        });
                },

                initSortable() {
                    // Destroy old instances
                    this.sortables.forEach(s => s.destroy());
                    this.sortables = [];

                    setTimeout(() => {
                        const rootList = document.getElementById('menu-list');
                        if (rootList) {
                            this.sortables.push(new Sortable(rootList, {
                                animation: 150,
                                handle: '.fa-grip-vertical',
                                ghostClass: 'ghost-sortable',
                                onEnd: () => { this.orderChanged = true; }
                            }));
                        }

                        const childLists = document.querySelectorAll('.child-sortable');
                        childLists.forEach(list => {
                            this.sortables.push(new Sortable(list, {
                                animation: 150,
                                handle: '.fa-grip-vertical',
                                ghostClass: 'ghost-sortable',
                                group: 'children',
                                onEnd: () => { this.orderChanged = true; }
                            }));
                        });
                    }, 100);
                },

                isAllRoles() {
                    return this.formData.allowed_roles.length === 0;
                },

                toggleAllRoles(e) {
                    if (e.target.checked) {
                        this.formData.allowed_roles = [];
                    }
                },

                openModal(mode, id = null) {
                    this.modalMode = mode;
                    this.formData = { id: '', parent_id: '', title: '', url: '#', icon: '', css_class: '', allowed_roles: [], is_active: 1 };
                    
                    if (mode === 'add' && id) {
                        // Adding a child
                        this.formData.parent_id = id;
                    } 
                    else if (mode === 'edit' && id) {
                        const menu = this.menus.find(m => m.id == id);
                        if (menu) {
                            this.formData = { ...menu };
                        }
                    }
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                submitForm(e) {
                    const form = e.target;
                    const formData = new FormData(form);
                    
                    if (this.formData.allowed_roles.length === 0) {
                        // If empty, remove it so it sends as null
                        formData.delete('allowed_roles[]');
                    }

                    fetch('api_menus.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'สำเร็จ', text: data.message, timer: 1500, showConfirmButton: false });
                            this.closeModal();
                            this.fetchMenus();
                        } else {
                            Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: data.message });
                        }
                    });
                },

                deleteMenu(id) {
                    Swal.fire({
                        title: 'ยืนยันการลบ?',
                        text: "หากลบแล้วจะไม่สามารถกู้คืนได้ (เมนูย่อยจะถูกลบไปด้วย)",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'ลบข้อมูล',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData();
                            formData.append('action', 'delete');
                            formData.append('id', id);
                            
                            fetch('api_menus.php', { method: 'POST', body: formData })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        Swal.fire({ icon: 'success', title: 'ลบสำเร็จ', showConfirmButton: false, timer: 1500 });
                                        this.fetchMenus();
                                    }
                                });
                        }
                    })
                },

                saveOrder() {
                    let orderData = [];
                    
                    // Root items
                    const rootItems = document.querySelectorAll('#menu-list > li[data-id]');
                    rootItems.forEach((li, index) => {
                        orderData.push({ id: li.dataset.id, order: index + 1 });
                    });

                    // Child items
                    const childLists = document.querySelectorAll('.child-sortable');
                    childLists.forEach(list => {
                        const parentId = list.dataset.parentId;
                        const items = list.querySelectorAll('li[data-id]');
                        items.forEach((li, index) => {
                            orderData.push({ id: li.dataset.id, order: index + 1, parent_id: parentId });
                        });
                    });

                    const formData = new FormData();
                    formData.append('action', 'reorder');
                    formData.append('order_data', JSON.stringify(orderData));

                    fetch('api_menus.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({ icon: 'success', title: 'บันทึกลำดับสำเร็จ', showConfirmButton: false, timer: 1500 });
                                this.orderChanged = false;
                                this.menus = [];
                                setTimeout(() => { this.fetchMenus(); }, 50);
                            }
                        });
                }
            }));
        });
    </script>
</body>
</html>
