<template>
  <div class="container-fluid">
    <!-- Statistics Card -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h5 class="card-title">Saldo Belum Ditarik</h5>
            <h3 class="card-text">{{ formatCurrency(statistics.my_pending_amount) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <h5 class="card-title">Pengajuan Pending</h5>
            <h3 class="card-text">{{ statistics.my_pending_withdrawals || 0 }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h5 class="card-title">Total Sudah Ditarik</h5>
            <h3 class="card-text">{{ formatCurrency(statistics.my_total_withdrawn) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Pending Distributions -->
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Data Komisi Belum Ditarik</h4>
        <div class="card-tools">
          <button class="btn btn-sm btn-primary" @click="selectAll" v-if="distributions.length > 0">
            {{ allSelected ? 'Batal Pilih Semua' : 'Pilih Semua' }}
          </button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
        
        <div v-else-if="distributions.length === 0" class="alert alert-info">
          Tidak ada komisi yang belum ditarik
        </div>
        
        <div v-else>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th width="50">
                    <input type="checkbox" v-model="allSelected" @change="toggleAllSelection">
                  </th>
                  <th>Tanggal Transaksi</th>
                  <th>Kode Transaksi</th>
                  <th>Customer</th>
                  <th>Peran</th>
                  <th>Jumlah Komisi</th>
                  <th>Persentase</th>
                  <th>Catatan</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="distribution in distributions" :key="distribution.id">
                  <td>
                    <input type="checkbox" 
                           :value="distribution.id" 
                           v-model="selectedDistributions"
                           @change="updateTotal">
                  </td>
                  <td>{{ formatDate(distribution.transaction?.transaction_date) }}</td>
                  <td>{{ distribution.transaction?.transaction_code }}</td>
                  <td>{{ distribution.transaction?.customer_name }}</td>
                  <td>
                    <span class="badge" :class="getRoleBadgeClass(distribution.role_type)">
                      {{ getRoleLabel(distribution.role_type) }}
                    </span>
                  </td>
                  <td class="text-right">{{ formatCurrency(distribution.amount) }}</td>
                  <td class="text-right">{{ distribution.percentage }}%</td>
                  <td>{{ distribution.calculation_note }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="5" class="text-right font-weight-bold">Total Dipilih:</td>
                  <td class="text-right font-weight-bold text-primary">{{ formatCurrency(selectedTotal) }}</td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Bank Information Form -->
          <div class="row mt-4" v-if="selectedDistributions.length > 0">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title">Informasi Rekening Penarikan</h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Nama Bank <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" v-model="bankInfo.bank_name" 
                               placeholder="Contoh: BCA, Mandiri, BRI">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Nama Pemilik Rekening <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" v-model="bankInfo.account_name"
                               placeholder="Nama sesuai rekening">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" v-model="bankInfo.account_number"
                               placeholder="Nomor rekening">
                      </div>
                    </div>
                  </div>
                  
                  <div class="alert alert-info">
                    <h6>Total Penarikan: {{ formatCurrency(selectedTotal) }}</h6>
                    <small>Pastikan informasi rekening sudah benar sebelum mengajukan penarikan</small>
                  </div>
                  
                  <button class="btn btn-success" @click="submitWithdrawal" :disabled="submitting">
                    <span v-if="submitting" class="spinner-border spinner-border-sm"></span>
                    Ajukan Penarikan
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- My Withdrawal History -->
    <div class="card mt-4">
      <div class="card-header">
        <h4 class="card-title">Riwayat Penarikan Saya</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Tanggal Pengajuan</th>
                <th>Total</th>
                <th>Status</th>
                <th>Bank</th>
                <th>Nomor Rekening</th>
                <th>Tanggal Diproses</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="withdrawal in myWithdrawals.data" :key="withdrawal.id">
                <td>{{ formatDate(withdrawal.requested_at) }}</td>
                <td>{{ formatCurrency(withdrawal.total_amount) }}</td>
                <td v-html="withdrawal.status_badge"></td>
                <td>{{ withdrawal.bank_name || '-' }}</td>
                <td>{{ withdrawal.account_number || '-' }}</td>
                <td>{{ withdrawal.processed_at ? formatDate(withdrawal.processed_at) : '-' }}</td>
                <td>
                  <button class="btn btn-sm btn-info" @click="viewWithdrawalDetail(withdrawal.id)">
                    Detail
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Pagination -->
          <div class="d-flex justify-content-center">
            <nav>
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: myWithdrawals.current_page === 1 }">
                  <a class="page-link" href="#" @click.prevent="loadMyWithdrawals(myWithdrawals.current_page - 1)">
                    Previous
                  </a>
                </li>
                <li v-for="page in myWithdrawals.last_page" 
                    :key="page" 
                    class="page-item" 
                    :class="{ active: myWithdrawals.current_page === page }">
                  <a class="page-link" href="#" @click.prevent="loadMyWithdrawals(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: myWithdrawals.current_page === myWithdrawals.last_page }">
                  <a class="page-link" href="#" @click.prevent="loadMyWithdrawals(myWithdrawals.current_page + 1)">
                    Next
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="withdrawalDetailModal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail Penarikan</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div v-if="selectedWithdrawal">
              <!-- Withdrawal Info -->
              <div class="row">
                <div class="col-md-6">
                  <table class="table table-sm">
                    <tr>
                      <th>Tanggal Pengajuan:</th>
                      <td>{{ formatDate(selectedWithdrawal.requested_at) }}</td>
                    </tr>
                    <tr>
                      <th>Status:</th>
                      <td v-html="selectedWithdrawal.status_badge"></td>
                    </tr>
                    <tr>
                      <th>Total:</th>
                      <td>{{ formatCurrency(selectedWithdrawal.total_amount) }}</td>
                    </tr>
                  </table>
                </div>
                <div class="col-md-6">
                  <table class="table table-sm">
                    <tr v-if="selectedWithdrawal.admin_fee">
                      <th>Biaya Admin:</th>
                      <td>{{ formatCurrency(selectedWithdrawal.admin_fee) }}</td>
                    </tr>
                    <tr>
                      <th>Jumlah Diterima:</th>
                      <td class="text-success font-weight-bold">
                        {{ formatCurrency(selectedWithdrawal.final_amount) }}
                      </td>
                    </tr>
                    <tr v-if="selectedWithdrawal.processed_by">
                      <th>Diproses Oleh:</th>
                      <td>{{ selectedWithdrawal.processor?.name }}</td>
                    </tr>
                  </table>
                </div>
              </div>

              <!-- Bank Info -->
              <div class="card mt-3">
                <div class="card-header">
                  <h6>Informasi Rekening</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-4">
                      <strong>Bank:</strong> {{ selectedWithdrawal.bank_name || '-' }}
                    </div>
                    <div class="col-md-4">
                      <strong>Nama Rekening:</strong> {{ selectedWithdrawal.account_name || '-' }}
                    </div>
                    <div class="col-md-4">
                      <strong>Nomor Rekening:</strong> {{ selectedWithdrawal.account_number || '-' }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Additional Info -->
              <div v-if="selectedWithdrawal.rejection_reason" class="alert alert-danger mt-3">
                <strong>Alasan Penolakan:</strong><br>
                {{ selectedWithdrawal.rejection_reason }}
              </div>

              <div v-if="selectedWithdrawal.notes" class="alert alert-info mt-3">
                <strong>Catatan:</strong><br>
                {{ selectedWithdrawal.notes }}
              </div>

              <!-- Distributions -->
              <div class="card mt-3">
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
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserWithdrawal',
  data() {
    return {
      loading: false,
      submitting: false,
      distributions: [],
      selectedDistributions: [],
      allSelected: false,
      selectedTotal: 0,
      bankInfo: {
        bank_name: '',
        account_name: '',
        account_number: ''
      },
      statistics: {},
      myWithdrawals: {
        data: [],
        current_page: 1,
        last_page: 1,
        total: 0
      },
      selectedWithdrawal: null
    }
  },
  mounted() {
    this.loadPendingDistributions();
    this.loadStatistics();
    this.loadMyWithdrawals();
  },
  methods: {
    async loadPendingDistributions() {
      this.loading = true;
      try {
        const response = await axios.get('/api/withdrawals/pending-distributions');
        this.distributions = response.data.data;
      } catch (error) {
        console.error('Error loading distributions:', error);
        this.$toast.error('Gagal memuat data distribusi');
      } finally {
        this.loading = false;
      }
    },
    
    async loadStatistics() {
      try {
        const response = await axios.get('/api/withdrawals/statistics');
        this.statistics = response.data.data;
      } catch (error) {
        console.error('Error loading statistics:', error);
      }
    },
    
    async loadMyWithdrawals(page = 1) {
      try {
        const response = await axios.get(`/api/withdrawals/my-withdrawals?page=${page}`);
        this.myWithdrawals = response.data.data;
      } catch (error) {
        console.error('Error loading withdrawals:', error);
        this.$toast.error('Gagal memuat riwayat penarikan');
      }
    },
    
    selectAll() {
      this.allSelected = !this.allSelected;
      if (this.allSelected) {
        this.selectedDistributions = this.distributions.map(d => d.id);
      } else {
        this.selectedDistributions = [];
      }
      this.updateTotal();
    },
    
    toggleAllSelection() {
      if (this.allSelected) {
        this.selectedDistributions = this.distributions.map(d => d.id);
      } else {
        this.selectedDistributions = [];
      }
      this.updateTotal();
    },
    
    updateTotal() {
      this.selectedTotal = this.distributions
        .filter(d => this.selectedDistributions.includes(d.id))
        .reduce((sum, d) => sum + parseFloat(d.amount), 0);
    },
    
    async submitWithdrawal() {
      // Validate bank info
      if (!this.bankInfo.bank_name || !this.bankInfo.account_name || !this.bankInfo.account_number) {
        this.$toast.error('Harap lengkapi informasi rekening');
        return;
      }
      
      if (this.selectedDistributions.length === 0) {
        this.$toast.error('Pilih minimal satu komisi untuk ditarik');
        return;
      }
      
      if (!confirm(`Ajukan penarikan sebesar ${this.formatCurrency(this.selectedTotal)}?`)) {
        return;
      }
      
      this.submitting = true;
      try {
        const response = await axios.post('/api/withdrawals/create', {
          distribution_ids: this.selectedDistributions,
          ...this.bankInfo
        });
        
        this.$toast.success(response.data.message);
        
        // Reset form
        this.selectedDistributions = [];
        this.allSelected = false;
        this.selectedTotal = 0;
        this.bankInfo = {
          bank_name: '',
          account_name: '',
          account_number: ''
        };
        
        // Reload data
        this.loadPendingDistributions();
        this.loadStatistics();
        this.loadMyWithdrawals();
        
      } catch (error) {
        console.error('Error submitting withdrawal:', error);
        if (error.response?.data?.errors) {
          Object.values(error.response.data.errors).forEach(err => {
            this.$toast.error(err[0]);
          });
        } else {
          this.$toast.error(error.response?.data?.message || 'Gagal mengajukan penarikan');
        }
      } finally {
        this.submitting = false;
      }
    },
    
    async viewWithdrawalDetail(id) {
      try {
        const response = await axios.get(`/api/withdrawals/${id}`);
        this.selectedWithdrawal = response.data.data;
        $('#withdrawalDetailModal').modal('show');
      } catch (error) {
        console.error('Error loading withdrawal detail:', error);
        this.$toast.error('Gagal memuat detail penarikan');
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
        day: 'numeric'
      });
    },
    
    getRoleLabel(roleType) {
      const labels = {
        inspector: 'Inspektor',
        coordinator: 'Koordinator',
        owner: 'Pemilik'
      };
      return labels[roleType] || roleType;
    },
    
    getRoleBadgeClass(roleType) {
      const classes = {
        inspector: 'badge-info',
        coordinator: 'badge-warning',
        owner: 'badge-success'
      };
      return classes[roleType] || 'badge-secondary';
    }
  }
}
</script>