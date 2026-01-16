<template>
  <div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <h5 class="card-title">Pending</h5>
            <h3 class="card-text">{{ formatCurrency(statistics.total_amount_pending) }}</h3>
            <p class="card-text mb-0">{{ statistics.total_pending }} pengajuan</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <h5 class="card-title">Disetujui</h5>
            <h3 class="card-text">{{ formatCurrency(statistics.total_amount_approved) }}</h3>
            <p class="card-text mb-0">{{ statistics.total_approved }} pengajuan</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h5 class="card-title">Selesai</h5>
            <h3 class="card-text">{{ formatCurrency(statistics.total_amount_completed) }}</h3>
            <p class="card-text mb-0">{{ statistics.total_completed }} pengajuan</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-danger text-white">
          <div class="card-body">
            <h5 class="card-title">Ditolak</h5>
            <h3 class="card-text">{{ statistics.total_amount_rejected || 0 }}</h3>
            <p class="card-text mb-0">{{ statistics.total_rejected }} pengajuan</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Status</label>
              <select class="form-control" v-model="filters.status" @change="loadWithdrawals">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
                <option value="processing">Diproses</option>
                <option value="completed">Selesai</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Cari</label>
              <input type="text" class="form-control" v-model="filters.search" 
                     placeholder="Cari berdasarkan nama, email, bank, atau nomor rekening..."
                     @keyup.enter="loadWithdrawals">
            </div>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" @click="loadWithdrawals">
              Filter
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Withdrawal Requests Table -->
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Pengajuan Penarikan</h4>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
        
        <div v-else-if="withdrawals.data.length === 0" class="alert alert-info">
          Tidak ada data pengajuan penarikan
        </div>
        
        <div v-else>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Tanggal Pengajuan</th>
                  <th>Pengaju</th>
                  <th>Bank</th>
                  <th>Nomor Rekening</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="withdrawal in withdrawals.data" :key="withdrawal.id">
                  <td>{{ formatDate(withdrawal.requested_at) }}</td>
                  <td>
                    <div>{{ withdrawal.user?.name }}</div>
                    <small class="text-muted">{{ withdrawal.user?.email }}</small>
                  </td>
                  <td>{{ withdrawal.bank_name }}</td>
                  <td>{{ withdrawal.account_number }}</td>
                  <td>{{ formatCurrency(withdrawal.total_amount) }}</td>
                  <td v-html="withdrawal.status_badge"></td>
                  <td>
                    <button class="btn btn-sm btn-info" @click="viewDetail(withdrawal)">
                      Detail
                    </button>
                    <button v-if="withdrawal.status === 'pending'" 
                            class="btn btn-sm btn-success ml-1"
                            @click="showProcessModal(withdrawal, 'approve')">
                      Proses
                    </button>
                    <button v-if="withdrawal.status === 'approved'"
                            class="btn btn-sm btn-primary ml-1"
                            @click="completeWithdrawal(withdrawal)">
                      Selesai
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div class="d-flex justify-content-center mt-3">
            <nav>
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: withdrawals.current_page === 1 }">
                  <a class="page-link" href="#" @click.prevent="loadWithdrawals(withdrawals.current_page - 1)">
                    Previous
                  </a>
                </li>
                <li v-for="page in withdrawals.last_page" 
                    :key="page" 
                    class="page-item" 
                    :class="{ active: withdrawals.current_page === page }">
                  <a class="page-link" href="#" @click.prevent="loadWithdrawals(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: withdrawals.current_page === withdrawals.last_page }">
                  <a class="page-link" href="#" @click.prevent="loadWithdrawals(withdrawals.current_page + 1)">
                    Next
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Process Modal -->
    <div class="modal fade" id="processModal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ processAction === 'approve' ? 'Setujui' : 'Tolak' }} Penarikan</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div v-if="selectedWithdrawal">
              <!-- Withdrawal Info -->
              <div class="alert alert-info">
                <h6>Detail Pengajuan</h6>
                <div class="row">
                  <div class="col-md-6">
                    <strong>Pengaju:</strong> {{ selectedWithdrawal.user?.name }}<br>
                    <strong>Email:</strong> {{ selectedWithdrawal.user?.email }}<br>
                    <strong>Total:</strong> {{ formatCurrency(selectedWithdrawal.total_amount) }}
                  </div>
                  <div class="col-md-6">
                    <strong>Bank:</strong> {{ selectedWithdrawal.bank_name }}<br>
                    <strong>Rekening:</strong> {{ selectedWithdrawal.account_number }}<br>
                    <strong>Nama Rekening:</strong> {{ selectedWithdrawal.account_name }}
                  </div>
                </div>
              </div>

              <!-- Distributions -->
              <div class="card mb-3">
                <div class="card-header">
                  <h6>Detail Komisi</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th>Kode Transaksi</th>
                          <th>Customer</th>
                          <th>Peran</th>
                          <th>Jumlah</th>
                          <th>Persentase</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="dist in selectedWithdrawal.transaction_distributions" :key="dist.id">
                          <td>{{ dist.transaction?.transaction_code }}</td>
                          <td>{{ dist.transaction?.customer_name }}</td>
                          <td>{{ getRoleLabel(dist.role_type) }}</td>
                          <td>{{ formatCurrency(dist.amount) }}</td>
                          <td>{{ dist.percentage }}%</td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="3" class="text-right font-weight-bold">Total:</td>
                          <td class="font-weight-bold">{{ formatCurrency(selectedWithdrawal.total_amount) }}</td>
                          <td></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Process Form -->
              <form @submit.prevent="processWithdrawal">
                <!-- Approval Form -->
                <div v-if="processAction === 'approve'">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Biaya Admin</label>
                        <input type="number" class="form-control" v-model="processForm.admin_fee"
                               placeholder="Masukkan biaya admin (opsional)">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Metode Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-control" v-model="processForm.payment_method" required>
                          <option value="">Pilih metode</option>
                          <option value="transfer">Transfer Bank</option>
                          <option value="cash">Tunai</option>
                          <option value="ewallet">E-Wallet</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label>Upload Bukti Transfer (Opsional)</label>
                    <input type="file" class="form-control" ref="fileInput" 
                           @change="handleFileUpload">
                    <small class="text-muted">Format: JPG, PNG, PDF (Max: 2MB)</small>
                  </div>
                  
                  <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <textarea class="form-control" v-model="processForm.notes" rows="3"
                              placeholder="Catatan untuk pengaju"></textarea>
                  </div>
                  
                  <div class="alert alert-success" v-if="processForm.admin_fee">
                    <strong>Total Penarikan:</strong> {{ formatCurrency(selectedWithdrawal.total_amount) }}<br>
                    <strong>Biaya Admin:</strong> {{ formatCurrency(processForm.admin_fee) }}<br>
                    <strong>Jumlah Diterima:</strong> {{ formatCurrency(selectedWithdrawal.total_amount - (processForm.admin_fee || 0)) }}
                  </div>
                </div>
                
                <!-- Rejection Form -->
                <div v-else>
                  <div class="form-group">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" v-model="processForm.rejection_reason" 
                              rows="4" required
                              placeholder="Berikan alasan penolakan yang jelas"></textarea>
                  </div>
                </div>
                
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                  <button type="submit" class="btn" 
                          :class="processAction === 'approve' ? 'btn-success' : 'btn-danger'"
                          :disabled="processing">
                    <span v-if="processing" class="spinner-border spinner-border-sm"></span>
                    {{ processAction === 'approve' ? 'Setujui' : 'Tolak' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminWithdrawal',
  data() {
    return {
      loading: false,
      processing: false,
      withdrawals: {
        data: [],
        current_page: 1,
        last_page: 1,
        total: 0
      },
      statistics: {},
      filters: {
        status: 'pending',
        search: ''
      },
      selectedWithdrawal: null,
      processAction: 'approve',
      processForm: {
        admin_fee: 0,
        payment_method: '',
        notes: '',
        rejection_reason: '',
        file: null
      }
    }
  },
  mounted() {
    this.loadStatistics();
    this.loadWithdrawals();
  },
  methods: {
    async loadStatistics() {
      try {
        const response = await axios.get('/api/withdrawals/statistics');
        this.statistics = response.data.data;
      } catch (error) {
        console.error('Error loading statistics:', error);
      }
    },
    
    async loadWithdrawals(page = 1) {
      this.loading = true;
      try {
        const params = {
          ...this.filters,
          page: page
        };
        const response = await axios.get('/api/withdrawals/requests', { params });
        this.withdrawals = response.data.data;
      } catch (error) {
        console.error('Error loading withdrawals:', error);
        this.$toast.error('Gagal memuat data pengajuan');
      } finally {
        this.loading = false;
      }
    },
    
    async viewDetail(withdrawal) {
      try {
        const response = await axios.get(`/api/withdrawals/${withdrawal.id}`);
        this.selectedWithdrawal = response.data.data;
        
        // Show detail in modal or new page
        // For simplicity, we can alert the details or open a modal
        alert(`
          Detail Penarikan:
          Pengaju: ${this.selectedWithdrawal.user?.name}
          Total: ${this.formatCurrency(this.selectedWithdrawal.total_amount)}
          Status: ${this.selectedWithdrawal.status}
          Bank: ${this.selectedWithdrawal.bank_name}
          Rekening: ${this.selectedWithdrawal.account_number}
        `);
      } catch (error) {
        console.error('Error loading detail:', error);
        this.$toast.error('Gagal memuat detail');
      }
    },
    
    showProcessModal(withdrawal, action) {
      this.processAction = action;
      this.selectedWithdrawal = withdrawal;
      this.resetProcessForm();
      
      if (action === 'approve') {
        // Load detailed data with distributions
        this.loadWithdrawalDetail(withdrawal.id);
      }
      
      $('#processModal').modal('show');
    },
    
    async loadWithdrawalDetail(id) {
      try {
        const response = await axios.get(`/api/withdrawals/${id}`);
        this.selectedWithdrawal = response.data.data;
      } catch (error) {
        console.error('Error loading withdrawal detail:', error);
      }
    },
    
    handleFileUpload(event) {
      this.processForm.file = event.target.files[0];
    },
    
    resetProcessForm() {
      this.processForm = {
        admin_fee: 0,
        payment_method: '',
        notes: '',
        rejection_reason: '',
        file: null
      };
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = null;
      }
    },
    
    async processWithdrawal() {
      if (this.processAction === 'reject' && !this.processForm.rejection_reason) {
        this.$toast.error('Harap isi alasan penolakan');
        return;
      }
      
      if (this.processAction === 'approve' && !this.processForm.payment_method) {
        this.$toast.error('Harap pilih metode pembayaran');
        return;
      }
      
      const actionText = this.processAction === 'approve' ? 'menyetujui' : 'menolak';
      if (!confirm(`Anda yakin ingin ${actionText} penarikan ini?`)) {
        return;
      }
      
      this.processing = true;
      try {
        const formData = new FormData();
        formData.append('action', this.processAction);
        
        if (this.processAction === 'approve') {
          formData.append('admin_fee', this.processForm.admin_fee || 0);
          formData.append('payment_method', this.processForm.payment_method);
          formData.append('notes', this.processForm.notes || '');
          if (this.processForm.file) {
            formData.append('file', this.processForm.file);
          }
        } else {
          formData.append('rejection_reason', this.processForm.rejection_reason);
        }
        
        const response = await axios.post(`/api/withdrawals/${this.selectedWithdrawal.id}/process`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        this.$toast.success(response.data.message);
        
        // Close modal
        $('#processModal').modal('hide');
        
        // Reload data
        this.loadStatistics();
        this.loadWithdrawals();
        
      } catch (error) {
        console.error('Error processing withdrawal:', error);
        if (error.response?.data?.errors) {
          Object.values(error.response.data.errors).forEach(err => {
            this.$toast.error(err[0]);
          });
        } else {
          this.$toast.error(error.response?.data?.message || 'Gagal memproses penarikan');
        }
      } finally {
        this.processing = false;
      }
    },
    
    async completeWithdrawal(withdrawal) {
      if (!confirm('Tandai penarikan sebagai selesai? Uang sudah dikirim ke pengaju.')) {
        return;
      }
      
      try {
        const response = await axios.post(`/api/withdrawals/${withdrawal.id}/complete`);
        this.$toast.success(response.data.message);
        this.loadStatistics();
        this.loadWithdrawals();
      } catch (error) {
        console.error('Error completing withdrawal:', error);
        this.$toast.error(error.response?.data?.message || 'Gagal menyelesaikan penarikan');
      }
    },
    
    formatCurrency(value) {
      if (!value) return 'Rp 0';
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      }).format(value);
    },
    
    formatDate(date) {
      if (!date) return '-';
      return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    getRoleLabel(roleType) {
      const labels = {
        inspector: 'Inspektor',
        coordinator: 'Koordinator',
        owner: 'Pemilik'
      };
      return labels[roleType] || roleType;
    }
  }
}
</script>