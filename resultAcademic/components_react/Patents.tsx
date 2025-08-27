import React, { useState, useMemo } from 'react';
import { Plus, Edit2, Trash2, X, Search, ChevronUp, ChevronDown, Lightbulb, Users, Calendar, FileText } from 'lucide-react';
import type { Patent } from '../types';

const mockPatents: Patent[] = [
  {
    id: '1',
    title: 'Quantum Computing Optimization Algorithm',
    inventors: ['John Doe', 'Jane Smith'],
    filingDate: '2024-01-15',
    patentNumber: 'US12345678',
    status: 'granted',
    description: 'A novel approach to optimize quantum computing calculations using advanced machine learning techniques.'
  },
  {
    id: '2',
    title: 'Sustainable Energy Storage System',
    inventors: ['Maria Garcia', 'David Chen'],
    filingDate: '2023-08-20',
    patentNumber: 'US87654321',
    status: 'pending',
    description: 'An innovative system for storing renewable energy using eco-friendly materials and advanced control mechanisms.'
  }
];

interface PatentFormData {
  title: string;
  inventors: string;
  filingDate: string;
  patentNumber: string;
  status: 'pending' | 'granted' | 'expired';
  description: string;
}

type SortField = 'title' | 'filingDate' | 'status';
type SortDirection = 'asc' | 'desc';

const FormField = ({ 
  label, 
  icon: Icon, 
  children 
}: { 
  label: string; 
  icon: React.ComponentType<{ size: number; className: string }>; 
  children: React.ReactNode 
}) => (
  <div className="space-y-1">
    <label className="block text-sm font-medium text-gray-700">
      <div className="flex items-center gap-2">
        <Icon size={16} className="text-gray-500" />
        {label}
      </div>
    </label>
    {children}
  </div>
);

const StatusBadge = ({ status }: { status: Patent['status'] }) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800',
    granted: 'bg-green-100 text-green-800',
    expired: 'bg-gray-100 text-gray-800'
  };

  return (
    <span className={`px-2 py-1 rounded-full text-xs font-medium ${colors[status]}`}>
      {status.charAt(0).toUpperCase() + status.slice(1)}
    </span>
  );
};

export const Patents = () => {
  const [patents, setPatents] = useState<Patent[]>(mockPatents);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortField, setSortField] = useState<SortField>('filingDate');
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [formData, setFormData] = useState<PatentFormData>({
    title: '',
    inventors: '',
    filingDate: '',
    patentNumber: '',
    status: 'pending',
    description: ''
  });

  const statuses = ['all', 'pending', 'granted', 'expired'];

  const filteredAndSortedPatents = useMemo(() => {
    return patents
      .filter(patent => {
        const matchesSearch = 
          patent.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
          patent.inventors.some(inventor => 
            inventor.toLowerCase().includes(searchQuery.toLowerCase())
          ) ||
          patent.patentNumber.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesStatus = statusFilter === 'all' || patent.status === statusFilter;

        return matchesSearch && matchesStatus;
      })
      .sort((a, b) => {
        let comparison = 0;
        if (sortField === 'filingDate') {
          comparison = new Date(a.filingDate).getTime() - new Date(b.filingDate).getTime();
        } else {
          comparison = a[sortField].localeCompare(b[sortField]);
        }
        return sortDirection === 'asc' ? comparison : -comparison;
      });
  }, [patents, searchQuery, sortField, sortDirection, statusFilter]);

  const handleSort = (field: SortField) => {
    if (sortField === field) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
    } else {
      setSortField(field);
      setSortDirection('asc');
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const patent: Patent = {
      id: editingId || Date.now().toString(),
      ...formData,
      inventors: formData.inventors.split(',').map(inventor => inventor.trim())
    };

    if (editingId) {
      setPatents(patents.map(p => p.id === editingId ? patent : p));
    } else {
      setPatents([...patents, patent]);
    }

    setShowForm(false);
    setEditingId(null);
    setFormData({
      title: '',
      inventors: '',
      filingDate: '',
      patentNumber: '',
      status: 'pending',
      description: ''
    });
  };

  const handleEdit = (patent: Patent) => {
    setFormData({
      ...patent,
      inventors: patent.inventors.join(', ')
    });
    setEditingId(patent.id);
    setShowForm(true);
  };

  const handleDelete = (id: string) => {
    if (confirm('Are you sure you want to delete this patent?')) {
      setPatents(patents.filter(p => p.id !== id));
    }
  };

  const SortIcon = ({ field }: { field: SortField }) => {
    if (sortField !== field) return null;
    return sortDirection === 'asc' ? <ChevronUp size={16} /> : <ChevronDown size={16} />;
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Patents</h2>
        <button
          onClick={() => setShowForm(true)}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
        >
          <Plus size={20} />
          Add Patent
        </button>
      </div>

      <div className="mb-6 space-y-4">
        <div className="flex flex-col sm:flex-row flex-wrap gap-4">
          <div className="w-full sm:flex-1 min-w-[200px]">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
              <input
                type="text"
                placeholder="Search patents..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
          <div className="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              {statuses.map(status => (
                <option key={status} value={status}>
                  {status === 'all' ? 'All Statuses' : status.charAt(0).toUpperCase() + status.slice(1)}
                </option>
              ))}
            </select>
          </div>
        </div>
        <div className="flex flex-wrap gap-4">
          <button
            onClick={() => handleSort('title')}
            className={`flex items-center gap-1 px-3 py-1 rounded-lg ${
              sortField === 'title' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'
            }`}
          >
            Title <SortIcon field="title" />
          </button>
          <button
            onClick={() => handleSort('filingDate')}
            className={`flex items-center gap-1 px-3 py-1 rounded-lg ${
              sortField === 'filingDate' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'
            }`}
          >
            Filing Date <SortIcon field="filingDate" />
          </button>
          <button
            onClick={() => handleSort('status')}
            className={`flex items-center gap-1 px-3 py-1 rounded-lg ${
              sortField === 'status' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'
            }`}
          >
            Status <SortIcon field="status" />
          </button>
        </div>
      </div>

      {showForm && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl p-4 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl sm:text-2xl font-bold text-gray-900">
                {editingId ? 'Edit Patent' : 'Add New Patent'}
              </h3>
              <button
                onClick={() => {
                  setShowForm(false);
                  setEditingId(null);
                }}
                className="text-gray-500 hover:text-gray-700 transition-colors"
              >
                <X size={24} />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="space-y-6">
              <FormField label="Title" icon={Lightbulb}>
                <input
                  type="text"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter patent title"
                  required
                />
              </FormField>

              <FormField label="Inventors" icon={Users}>
                <input
                  type="text"
                  value={formData.inventors}
                  onChange={(e) => setFormData({ ...formData, inventors: e.target.value })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter inventors (comma-separated)"
                  required
                />
              </FormField>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <FormField label="Filing Date" icon={Calendar}>
                  <input
                    type="date"
                    value={formData.filingDate}
                    onChange={(e) => setFormData({ ...formData, filingDate: e.target.value })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    required
                  />
                </FormField>

                <FormField label="Patent Number" icon={Lightbulb}>
                  <input
                    type="text"
                    value={formData.patentNumber}
                    onChange={(e) => setFormData({ ...formData, patentNumber: e.target.value })}
                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                    placeholder="Enter patent number"
                    required
                  />
                </FormField>
              </div>

              <FormField label="Status" icon={Lightbulb}>
                <select
                  value={formData.status}
                  onChange={(e) => setFormData({ ...formData, status: e.target.value as Patent['status'] })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  required
                >
                  <option value="pending">Pending</option>
                  <option value="granted">Granted</option>
                  <option value="expired">Expired</option>
                </select>
              </FormField>

              <FormField label="Description" icon={FileText}>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  rows={4}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter patent description"
                  required
                />
              </FormField>

              <div className="flex flex-col sm:flex-row justify-end gap-4 pt-4">
                <button
                  type="button"
                  onClick={() => {
                    setShowForm(false);
                    setEditingId(null);
                  }}
                  className="w-full sm:w-auto px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                  {editingId ? 'Update Patent' : 'Save Patent'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {filteredAndSortedPatents.map((patent) => (
          <div key={patent.id} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex justify-between items-start mb-4">
              <div>
                <h3 className="text-lg font-semibold text-gray-900">{patent.title}</h3>
                <div className="text-sm text-gray-600 mt-1">
                  {patent.inventors.join(', ')}
                </div>
                <div className="text-sm text-gray-500 mt-1">
                  Patent No: {patent.patentNumber}
                </div>
                <div className="text-sm text-gray-500">
                  Filed: {new Date(patent.filingDate).toLocaleDateString()}
                </div>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => handleEdit(patent)}
                  className="text-blue-600 hover:text-blue-900"
                >
                  <Edit2 size={18} />
                </button>
                <button
                  onClick={() => handleDelete(patent.id)}
                  className="text-red-600 hover:text-red-900"
                >
                  <Trash2 size={18} />
                </button>
              </div>
            </div>
            <div className="mb-3">
              <StatusBadge status={patent.status} />
            </div>
            <p className="text-gray-700">{patent.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};