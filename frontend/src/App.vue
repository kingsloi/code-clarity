<template>
<div class="container-fluid mt-3">
    <div class="d-grid gap-3" style="grid-template-columns: 2fr 2fr;"
      :class="{ 'blurred': loading === true }"
    >
      <div class="media-preview">
        <div class="sticky-top">

          <small class="d-block mb-2 text-muted text-uppercase">scanned documents</small>
          <div class="input-group mb-2">
            <select class="form-select" id="inputGroupSelect04"
              @change="onScanChange($event)"
              :value="scan.id || null"
            >
              <option disabled selected>--------</option>
              <option
                v-for="(s, index) in scans" :key="s.id" :value="s.id"
              >
                statement: {{ (s.page && s.page.statement) ? s.page.statement.id : '(unlinked)' }} --
                {{ s.document.file_name }} --
                visit: {{ (s.page && s.page.statement) ? s.page.statement.visitId : '' }} --
                page#: {{ (s.page && s.page.pageNumber) ? s.page.pageNumber : '' }}
              </option>
            </select>
          </div>

          <small class="d-block mt-3 mb-2 text-muted text-uppercase">preview</small>
          <iframe
            style="width: 100%; height: 800px;"
            :src="`http://localhost:8000/scans/${scan.id}/pdf#toolbar=0&navpanes=0&scrollbar=0`"
            class="mw-100"
            v-if="scan.id"
          />
        </div>
      </div>

      <div class="form-filla">

        <small class="d-block mb-2 text-muted text-uppercase">statements</small>
        <div class="input-group mb-2">
          <select class="form-select" id="inputGroupSelect04"
            @change="onStatementChange($event)"
            :value="statement.id || null"
          >
            <option disabled selected>--------</option>
            <option
              v-for="(s, index) in statements" :key="s.id" :value="s.id"
            >
              statement: {{ s.id }} -- visit: {{ s.visitId }} -- date: {{ s.serviceDate || '-' }} -- pages: {{ s.pages.length }}
            </option>
          </select>
          <button class="btn btn-outline-success" type="button" @click.prevent="addStatement()"><i class="bi bi-file-plus"></i></button>
          <button class="btn btn-outline-danger" type="button" @click.prevent="deleteStatement(statement.id)"><i class="bi bi-file-minus" :disabled="statement.id === null" v-if="statement"></i></button>
        </div>

        <small class="d-block mt-3 mb-2 text-muted text-uppercase">statement</small>
        <div class="border p-3 bg-light">
          <h2 class="text-start text-uppercase h3">

            <a href="#" @click.prevent="linkPageToScan()" class="fs-2 pe-2"
              v-if="page && scan"
            >
              <i class="bi"
                :class="{
                  'bi-magnet': scan.page === null,
                  'bi-magnet-fill magnet-connected': scan.page !== null
                }"
              ></i>
            </a>

            <a href="#" @click.prevent="ocr = ! ocr" class="fs-2 pe-2"
              v-if="page && scan"
            >
              <i class="bi"
                :class="{
                  'bi-eye': ocr === false,
                  'bi-eye-fill': ocr === true
                }"
              ></i>
            </a>

            <span>
              Page: {{ (page && page.pageNumber ? page.pageNumber : '-') }}
            </span>
          </h2>

          <form class="row g-3 mt-5" v-if="statement">

            <div class="col-3">
              <label for="accountID" class="form-label">Account ID:</label>
              <input type="text" class="form-control form-control-sm" v-model="statement.accountId" :disabled="pageNumber > 1"
                @blur="saveStatementField($event, statement.id, 'accountId')"
              />
            </div>

            <div class="w-100"></div>

            <div class="col-3">
              <label for="visitId" class="form-label pt-3">Visit ID:</label>

              <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="page.scan.document.visit_id" readonly
                v-if="page && scan && ocr"
                @click="(event) => this.statement.visitId = event.target.value"
              />

              <input type="text" class="form-control form-control-sm" v-model="statement.visitId" :disabled="pageNumber > 1"
                @blur="saveStatementField($event, statement.id, 'visitId')"
              />
            </div>

            <div class="w-100"></div>

            <div class="row gx-3 gy-0">
              <div class="col-5">

                <div>
                  <label for="accountClass" class="form-label">Account Class:</label>

                  <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="page.scan.document.account_class" readonly
                    v-if="page && scan && ocr"
                    @click="(event) => this.statement.accountClass = event.target.value"
                  />

                  <input type="text" class="form-control form-control-sm" v-model="statement.accountClass" :disabled="pageNumber > 1"
                    @blur="saveStatementField($event, statement.id, 'accountClass')"
                  />
                </div>

                <div>
                  <label for="attendingPhysician" class="form-label pt-3">Attending Physician:</label>

                  <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="page.scan.document.attending_physician" readonly
                    v-if="page && scan && ocr"
                    @click="(event) => this.statement.attendingPhysician = event.target.value"
                  />

                  <auto-complete-field type="statement" column="attendingPhysician"
                    v-model="statement.attendingPhysician"
                    :disabled="pageNumber > 1"
                    ref="attendingPhysician"
                    @interface="getChildInterface"
                    @blur="saveStatementField($event, statement.id, 'attendingPhysician')"
                  />
                </div>
              </div>

              <div class="col-5 offset-2">
                <div class="w-100">
                  <label for="totalCharges" class="form-label"
                    :class="{
                      'fw-bold text-danger': statement.totalCharges && statement.totalCharges !== statementTotalCharges,
                      'fw-bold text-success': statement.totalCharges && statement.totalCharges === statementTotalCharges,
                    }"
                  >
                    Total Charges:
                  </label>

                  <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="page.scan.document.total_charges" readonly
                    v-if="page && scan && ocr"
                    @click="(event) => this.statement.totalCharges = event.target.value"
                  />

                  <input type="number" class="form-control form-control-sm" v-model="statement.totalCharges" :disabled="pageNumber > 1"
                    :class="{
                      'is-invalid': statement.totalCharges && statement.totalCharges !== statementTotalCharges,
                      'is-valid': statement.totalCharges && statement.totalCharges === statementTotalCharges,
                    }"
                    @blur="saveStatementField($event, statement.id, 'totalCharges')"
                  />

                  <!-- feedback if mismatch -->
                  <div class="invalid-feedback d-block"
                    v-if="statement.totalCharges && statement.totalCharges !== statementTotalCharges"
                  >
                    total charges mismatch! difference = {{ (statement.totalCharges - statementTotalCharges).toFixed(2) }}
                  </div>
                </div>

                <div>
                  <label for="serviceDate" class="form-label pt-3">Service Date</label>

                  <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="page.scan.document.service_date" readonly
                    v-if="page && scan && ocr"
                    @click="(event) => this.statement.serviceDate = event.target.value"
                  />

                  <input type="date" class="form-control form-control-sm" v-model="statement.serviceDate" :disabled="pageNumber > 1"
                    @blur="saveStatementField($event, statement.id, 'serviceDate')"
                  />
                </div>
              </div>
            </div>

            <h2 class="h6 mb-0 pb-0 pt-3 fw-bold">Professional Charges</h2>

            <div class="col-12">
              <table class="table table-sm">
                <thead>
                  <tr class="table-dark text-center">
                    <th scope="col">Date</th>
                    <th scope="col">Rev Code</th>
                    <th scope="col">Procedure<br/>Code</th>
                    <th scope="col">Description</th>
                    <th scope="col" style="width: 60px;">Qty</th>
                    <th scope="col">Amount</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(charge, i) in charges" :key="charge.id">
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getChargeOCRValue(i, 'key_0')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.charges[i].date = event.target.value"
                      />
                      <input type="date" class="form-control form-control-sm text-center" v-model="charges[i].date"
                        @blur="saveCharge($event, charge.id, 'date')"
                      />
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getChargeOCRValue(i, 'key_1')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.charges[i].revCode = event.target.value"
                      />

                      <input type="text" class="form-control form-control-sm text-center" v-model="charges[i].revCode"
                        @blur="saveCharge($event, charge.id, 'revCode')"
                      />
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getChargeOCRValue(i, 'key_2')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.charges[i].procedureCode = event.target.value"
                      />

                      <input type="text" class="form-control form-control-sm text-center" v-model="charges[i].procedureCode"
                        @blur="saveCharge($event, charge.id, 'procedureCode')"
                      />
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getChargeOCRValue(i, 'key_3')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.charges[i].description = event.target.value"
                      />

                      <auto-complete-field type="charge" column="description"
                        v-model="charges[i].description"
                        :ref="`charges-${i}-description`"
                        @blur="saveCharge($event, charge.id, 'description')"
                        style="width: 300px;"
                        @interface="getChildInterface"
                      />
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getChargeOCRValue(i, 'key_4')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.charges[i].qty = event.target.value"
                      />

                      <input type="number" class="form-control form-control-sm text-center" v-model="charges[i].qty"
                        @blur="saveCharge($event, charge.id, 'qty')"
                      />
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getChargeOCRValue(i, 'key_5')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.charges[i].amount = event.target.value"
                      />

                      <input type="number" class="form-control form-control-sm text-end" v-model="charges[i].amount"
                        @blur="saveCharge($event, charge.id, 'amount')"
                      />
                    </td>
                    <td class="">
                      <a href="#" @click.prevent="deleteCharge(charge.id)">
                        <i class="bi bi-trash "></i>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="7" class="text-center text-uppercase">
                      <a href="#" @click.prevent="addCharge(page.id)">Add Row</a>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="fw-bold">
                    <td scope="col" colspan="4">Total Professional Charges:</td>
                    <td scope="col" colspan="3">
                      <input type="number" class="form-control form-control-sm text-end" v-model="statement.totalCharges" :disabled="pageNumber > 1"
                        :class="{
                          'is-invalid': typeof(statement.totalCharges) !== 'undefined' && statement.totalCharges !== statementTotalCharges,
                          'is-valid': typeof(statement.totalCharges) !== 'undefined' && statement.totalCharges === statementTotalCharges,
                        }"
                        @blur="saveStatementField($event, statement.id, 'totalCharges')"
                      />
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <h2 class="h6 mb-0 pb-0 pt-3 fw-bold">Professional Payments and Adjustments</h2>

            <div class="col-12">
              <table class="table table-sm">
                <thead>
                  <tr class="table-dark text-center">
                    <th scope="col">Date</th>
                    <th scope="col" style="width: 600px;">Description</th>
                    <th scope="col">Amount</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(payment, i) in payments" :key="payment.id">
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getPaymentOCRValue(i, 'key_0')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.payments[i].date = event.target.value"
                      />

                      <input type="date" class="form-control form-control-sm" v-model="payments[i].date"
                        @blur="savePayment($event, payment.id, 'date')"
                      />
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getPaymentOCRValue(i, 'key_1')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.payments[i].description = event.target.value"
                      />

                      <auto-complete-field type="payment" column="description"
                        v-model="payments[i].description"
                        :ref="`payments-${i}-description`"
                        @blur="savePayment($event, payment.id, 'description')"
                        @interface="getChildInterface"
                      />
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="getPaymentOCRValue(i, 'key_2')" readonly
                        v-if="page && scan && ocr"
                        tabindex="-1"
                        @click="(event) => this.payments[i].amount = event.target.value"
                      />

                      <input type="number" class="form-control form-control-sm text-end" v-model="payments[i].amount"
                        @blur="savePayment($event, payment.id, 'amount')"
                      />
                    </td>
                    <td class="">
                      <a href="#" @click.prevent="deletePayment(payment.id)">
                        <i class="bi bi-trash "></i>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-center text-uppercase">
                      <a href="#" @click.prevent="addPayment(page.id)">Add Row</a>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="fw-bold">
                    <td scope="col" colspan="2">Total Professional Payments and Adjustments:</td>
                    <td scope="col" colspan="2">
                      <input type="number" class="form-control form-control-sm text-end" v-model="statement.totalPayments" :disabled="pageNumber > 1"
                        :class="{
                          'is-invalid': typeof(statement.totalPayments) !== 'undefined' && statement.totalPayments !== statementTotalPayments,
                          'is-valid': typeof(statement.totalPayments) !== 'undefined' && statement.totalPayments === statementTotalPayments,
                        }"
                        @blur="saveStatementField($event, statement.id, 'totalPayments')"
                      />
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div class="row mb-3 gx-2 mt-5 justify-content-end">
              <label for="inputEmail3" class="col-sm-8 text-end col-form-label"
                :class="{
                  'fw-bold text-danger': typeof(statement.totalBalance) !== 'undefined' && statement.totalBalance !== statementTotalBalance,
                  'fw-bold text-success': typeof(statement.totalBalance) !== 'undefined' && statement.totalBalance === statementTotalBalance,
                }"
              >
                Total Balance:
              </label>

              <div class="col-sm-2 offset-1">

                  <input type="text" class="form-control form-control-sm mb-1 text-muted bg-light" :value="page.scan.document.total_balance" readonly
                    v-if="page && scan && ocr"
                    @click="(event) => this.statement.totalBalance = event.target.value"
                  />

                <input type="number" class="form-control form-control-sm" v-model="statement.totalBalance" :disabled="pageNumber > 1"
                  :class="{
                    'is-invalid': typeof(statement.totalBalance) !== 'undefined' && statement.totalBalance !== statementTotalBalance,
                    'is-valid': typeof(statement.totalBalance) !== 'undefined' && statement.totalBalance === statementTotalBalance,
                  }"
                  @blur="saveStatementField($event, statement.id, 'totalBalance')"
                />
              </div>

              <!-- feedback if mismatch -->
              <div class="invalid-feedback d-block text-end"
                v-if="typeof(statement.totalBalance) !== 'undefined' && statementTotalBalance !== statement.totalBalance"
              >
                total balance mismatch! difference = {{ (statementTotalBalance - statement.totalBalance).toFixed(2) }}
              </div>
            </div>

            <h2 class="h6 text-uppercase text-center mb-0">Pages</h2>

            <div class="row g-3 align-items-center align-self-center justify-content-center mb-0">
              <div class="col-1">
                <input type="number" class="form-control form-control-sm  text-center" v-model="pageNumber">
              </div>
              <div class="col-auto">
                Of
              </div>
              <div class="col-1">
                <input type="number" class="form-control form-control-sm  text-center" v-model="statement.totalPages" :disabled="pageNumber > 1"
                  @blur="saveStatementField($event, statement.id, 'totalPages')"
                />
              </div>
            </div>

            <div class="row g-3 align-items-center align-self-center justify-content-center mt-0">
              <div class="col-auto">
                <button type="button" class="btn btn-sm btn-secondary" @click.prevent="pagePrevious()" :disabled="pageNumber === 1">&lt; Prev</button>
              </div>
              <div class="col-auto">
                <button type="button" class="btn btn-sm btn-secondary ms-2" @click.prevent="pageNext()" :disabled="pageNumber === this.statement.totalPages">Next &gt;</button>
              </div>
            </div>

            <div class="row align-items-center mt-4">
              <div class="w-50 mx-auto">
                <ul class="list-group">

                  <li class="list-group-item d-flex justify-content-between align-items-start" v-for="page in pages"
                    :class="{ 'list-group-item-primary': page.pageNumber === pageNumber }"
                  >
                    <div class="ms-2 me-auto">
                      <a href="#" class="text-dark" @click.prevent="changePage(page.pageNumber)">
                        Page: {{ page.pageNumber }}
                      </a>
                    </div>

                    <span class="badge rounded-pill text-bg-danger ms-1" v-if="page.pageNumber !== 1">
                      <a href="#" @click.prevent="deletePage(page.id)" class="d-block">
                        <i class="bi bi-trash text-white"></i>
                      </a>
                    </span>

                    <span class="badge rounded-pill text-bg-success ms-1">{{ page.charges.length }}</span>
                    <span class="badge rounded-pill text-bg-warning ms-1">{{ page.payments.length }}</span>
                  </li>

                  <li class="list-group-item">
                    <a href="#" @click.prevent="createNewPage(statement.id)">Add Page</a>
                  </li>

                </ul>
              </div>
            </div>

          </form>

          <div class="overflow-scroll mt-5" v-if="scan && ocr">
            <pre  class="w-100 json-viewer">{{ scan.document }}</pre>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
const axios = require('axios').default;
import AutoCompleteField from './components/AutoCompleteField.vue';

export default {
  name: 'App',

  components: {
    AutoCompleteField
  },

  mounted() {
    (async () => {
      await this.getAllStatements();
      await this.getAllScans();
    })();
  },

  data() {
    return {
      ocr: false,

      loading: true,

      pageNumber: 1,

      statements: [],
      statement: { id: null, pages: [], },

      scans: [],
      scan: { id: null, document: {} },
    }
  },

  computed: {
    pages() {
      return this.statement.pages || [];
    },

    page() {
      return this.pages.find(p => p.pageNumber === this.pageNumber);
    },

    charges() {
      if (this.page) {
        return this.page.charges;
      }
      return [];
    },

    payments() {
      if (this.page) {
        return this.page.payments;
      }
      return [];
    },

    statementTotalCharges() {
      return this.pages.reduce((sum, page) => {
        const { charges } = page;
        return charges.reduce((pageSum, charge) => {
          return pageSum + parseFloat(charge.amount || 0);
        }, sum)
      }, 0);
    },

    statementTotalPayments() {
      return this.pages.reduce((sum, page) => {
        const { payments } = page;
        return payments.reduce((pageSum, payment) => {
          return pageSum + parseFloat(payment.amount || 0);
        }, sum)
      }, 0);
    },

    statementTotalBalance() {
      let payments;
       if (this.statement.totalPayments - this.statementTotalPayments === 0) {
        payments = this.statement.totalPayments;
      } else {
        payments = this.statement.totalPayments - this.statementTotalPayments
      }

      return this.statementTotalCharges - payments;
    },
  },

  methods: {
    getChargeOCRValue(index, key) {
      const { scan: { document: charges } }  = this;
      const { charges: ocr } = charges;

      if (ocr[index] && ocr[index][key]) {
        return ocr[index][key];
      }

      return 'N/A';
    },

    getPaymentOCRValue(index, key) {
      let payments = [];
      const { page: { scan: { document: doc } } } = this;

      if (doc.payments) {
        payments = doc.payments;
      }

      if (payments[index] && payments[index][key]) {
        return payments[index][key];
      }

      return 'N/A';
    },

    pagePrevious() {
      if (this.pageNumber > 1) {
        this.changePage(this.pageNumber - 1);
      }
    },

    pageNext() {
      if (this.pageNumber < this.statement.totalPages) {
        this.changePage(this.pageNumber + 1);
      }
    },

    changePage(pageNumber) {
      this.pageNumber = pageNumber;
      this.$options.childInterface.clearAutoComplete();

      if (this.page && this.page.scan) {
        const { page: { scan: { id: scanId } } } = this;
        this.setScan(scanId);
      }
    },

    onStatementChange($event) {
      const { target: { value: statementId } } = $event;

      this.setStatement(statementId);
      this.changePage(1);
    },

    setStatement(statementId, column = 'id') {
      this.statement = this.statements.find(s => s[column] === parseInt(statementId));
    },

    setScan(scanId, column = 'id') {
      this.scan = this.scans.find(s => s.id === parseInt(scanId));
    },

    onScanChange($event) {
      const { target: { value: scanId } } = $event;

      this.setScan(scanId);

      if (this.scan.page && this.scan.page.statement) {
        const { page: { statement: { id: statementId } } } = this.scan;
        const { page: { pageNumber } } = this.scan;
        this.setStatement(statementId);
        this.changePage(pageNumber);
      }
    },

    getChildInterface(childInterface) {
      this.$options.childInterface = childInterface;
    },

    async getAllStatements() {
      try {
        let { data } = await axios.get('http://localhost:8000/api/statements');
        this.loading = false;
        this.statements = data;
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async getAllScans() {
      try {
        let { data } = await axios.get('http://localhost:8000/api/scans');
        this.loading = false;
        this.scans = data;
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async linkPageToScan() {
      const { id: scanId } = this.scan;
      const { id: pageId } = this.page;
      const { id: statementId } = this.statement;

      try {
        let { data } = await axios.put(`http://localhost:8000/api/pages/${pageId}/scan/${scanId}`);

        await this.getAllScans();
        await this.getAllStatements();

        this.setScan(scanId);
        this.setStatement(statementId);

        this.loading = false;
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async addStatement() {
      this.loading = true;
      try {
        let { data } = await axios.post('http://localhost:8000/api/statements');

        this.loading = false;
        this.statements = data;
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async deleteStatement(statementId) {
      this.loading = true;
      try {
        let { data } = await axios.delete(`http://localhost:8000/api/statements/${statementId}`);

        this.loading = false;
        this.statements = data;
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async saveStatementField($event, statementId, field) {
      const { target: { value } } = $event;

      try {
        let { data } = await axios.put(`http://localhost:8000/api/statements/${statementId}`, { [field]: value });

        this.loading = false;
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async createNewPage(statementId) {
      try {
        let { data } = await axios.post(`http://localhost:8000/api/statements/${statementId}/page`);
        this.loading = false;
        this.statements = data;
        this.setStatement(statementId);
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async deletePage(pageId) {
      const { id: statementId } = this.statement;
      try {
        let { data } = await axios.delete(`http://localhost:8000/api/pages/${pageId}`);
        this.loading = false;
        this.statements = data;
        this.setStatement(statementId);
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async addCharge(pageId) {
      const { id: statementId } = this.statement;
      try {
        let { data } = await axios.post(`http://localhost:8000/api/pages/${pageId}/charge`);
        this.loading = false;
        this.statements = data;
        this.setStatement(statementId);
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async deleteCharge(chargeId) {
      const { id: statementId } = this.statement;
      try {
        let { data } = await axios.delete(`http://localhost:8000/api/charges/${chargeId}`);
        this.loading = false;
        this.statements = data;
        this.setStatement(statementId);
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async saveCharge($event, chargeId, field) {
      const { target: { value } } = $event;

      try {
        let { data } = await axios.put(`http://localhost:8000/api/charges/${chargeId}`, { [field]: value });

        this.loading = false;
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async addPayment(pageId) {
      const { id: statementId } = this.statement;
      try {
        let { data } = await axios.post(`http://localhost:8000/api/pages/${pageId}/payment`);
        this.loading = false;
        this.statements = data;
        this.setStatement(statementId);
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async deletePayment(paymentId) {
      const { id: statementId } = this.statement;
      try {
        let { data } = await axios.delete(`http://localhost:8000/api/payments/${paymentId}`);
        this.loading = false;
        this.statements = data;
        this.setStatement(statementId);
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },

    async savePayment($event, paymentId, field) {
      const { target: { value } } = $event;

      try {
        let { data } = await axios.put(`http://localhost:8000/api/payments/${paymentId}`, { [field]: value });

        this.loading = false;
      } catch (err) {
        alert('error! Check console.');
        console.log(err)
      }
    },
  }
}
</script>

<style>
  .d-grid {
    min-height: calc(100vh - 100px);
  }
</style>
