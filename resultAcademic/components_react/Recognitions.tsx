import React, { useState, useMemo } from 'react';
import { Plus, Edit2, Trash2, X, Award, FileText, Search, ChevronUp, ChevronDown } from 'lucide-react';
import type { Recognition } from '../types';

const mockRecognitions: Recognition[] = [
  {
    id: '1',
    title: 'Outstanding Research Contribution',
    institution: 'International Science Foundation',
    date: '2024-02-15',
    description: 'Awarded for exceptional contributions to quantum computing research and its applications in machine learning.'
  },
  {
    id: '2',
    title: 'Excellence in Innovation',
    institution: 'Global Technology Institute',
    date: '2023-11-10',
    description: 'Recognition for innovative approaches in sustainable energy storage solutions.'
  }
];

interface RecognitionFormData {
  title: string;
  institution: string;
  date: string;
  description: string;
}

type SortField = 'title' | 'institution' | 'date';
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

export const Recognitions = () => {
  const [recognitions, setRecognitions] = useState<Recognition[]>(mockRecognitions);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortField, setSortField] = useState<SortField>('date');
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc');
  const [institutionFilter, setInstitutionFilter] = useState<string>('all');
  const [yearFilter, setYearFilter] = useState<string>('all');
  const [formData, setFormData] = useState<RecognitionFormData>({
    title: '',
    institution: '',
    date: '',
    description: ''
  });

  const institutions = useMemo(() => 
    ['all', ...new Set(recognitions.map(r => r.institution))],
    [recognitions]
  );

  const years = useMemo(() => 
    ['all', ...new Set(recognitions.map(r => 
      new Date(r.date).getFullYear().toString()
    ))].sort((a, b) => b.localeCompare(a)),
    [recognitions]
  );

  const filteredAndSortedRecognitions = useMemo(() => {
    return recognitions
      .filter(recognition => {
        const matchesSearch = 
          recognition.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
          recognition.institution.toLowerCase().includes(searchQuery.toLowerCase()) ||
          recognition.description.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesInstitution = institutionFilter === 'all' || recognition.institution === institutionFilter;
        const matchesYear = yearFilter === 'all' || new Date(recognition.date).getFullYear().toString() === yearFilter;

        return matchesSearch && matchesInstitution && matchesYear;
      })
      .sort((a, b) => {
        let comparison = 0;
        if (sortField === 'date') {
          comparison = new Date(a.date).getTime() - new Date(b.date).getTime();
        } else {
          comparison = a[sortField].localeCompare(b[sortField]);
        }
        return sortDirection === 'asc' ? comparison : -comparison;
      });
  }, [recognitions, searchQuery, sortField, sortDirection, institutionFilter, yearFilter]);

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
    const recognition: Recognition = {
      id: editingId || Date.now().toString(),
      ...formData
    };

    if (editingId) {
      setRecognitions(recognitions.map(r => r.id === editingId ? recognition : r));
    } else {
      setRecognitions([...recognitions, recognition]);
    }

    setShowForm(false);
    setEditingId(null);
    setFormData({
      title: '',
      institution: '',
      date: '',
      description: ''
    });
  };

  const handleEdit = (recognition: Recognition) => {
    setFormData({
      ...recognition
    });
    setEditingId(recognition.id);
    setShowForm(true);
  };

  const handleDelete = (id: string) => {
    if (confirm('Are you sure you want to delete this recognition?')) {
      setRecognitions(recognitions.filter(r => r.id !== id));
    }
  };

  const SortIcon = ({ field }: { field: SortField }) => {
    if (sortField !== field) return null;
    return sortDirection === 'asc' ? <ChevronUp size={16} /> : <ChevronDown size={16} />;
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Recognitions & Honors</h2>
        <button
          onClick={() => setShowForm(true)}
          className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
        >
          <Plus size={20} />
          Add Recognition
        </button>
      </div>

      <div className="mb-6 space-y-4">
        <div className="flex flex-col sm:flex-row flex-wrap gap-4">
          <div className="w-full sm:flex-1 min-w-[200px]">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
              <input
                type="text"
                placeholder="Search recognitions..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
          <div className="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <select
              value={institutionFilter}
              onChange={(e) => setInstitutionFilter(e.target.value)}
              className="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              {institutions.map(institution => (
                <option key={institution} value={institution}>
                  {institution === 'all' ? 'All Institutions' : institution}
                </option>
              ))}
            </select>
            <select
              value={yearFilter}
              onChange={(e) => setYearFilter(e.target.value)}
              className="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              {years.map(year => (
                <option key={year} value={year}>
                  {year === 'all' ? 'All Years' : year}
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
            onClick={() => handleSort('institution')}
            className={`flex items-center gap-1 px-3 py-1 rounded-lg ${
              sortField === 'institution' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'
            }`}
          >
            Institution <SortIcon field="institution" />
          </button>
          <button
            onClick={() => handleSort('date')}
            className={`flex items-center gap-1 px-3 py-1 rounded-lg ${
              sortField === 'date' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'
            }`}
          >
            Date <SortIcon field="date" />
          </button>
        </div>
      </div>

      {showForm && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl p-4 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl sm:text-2xl font-bold text-gray-900">
                {editingId ? 'Edit Recognition' : 'Add New Recognition'}
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
              <FormField label="Title" icon={Award}>
                <input
                  type="text"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter recognition title"
                  required
                />
              </FormField>

              <FormField label="Institution" icon={Award}>
                <input
                  type="text"
                  value={formData.institution}
                  onChange={(e) => setFormData({ ...formData, institution: e.target.value })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter institution name"
                  required
                />
              </FormField>

              <FormField label="Date" icon={Award}>
                <input
                  type="date"
                  value={formData.date}
                  onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  required
                />
              </FormField>

              <FormField label="Description" icon={FileText}>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  rows={4}
                  className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Enter recognition description"
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
                  {editingId ? 'Update Recognition' : 'Save Recognition'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {filteredAndSortedRecognitions.map((recognition) => (
          <div key={recognition.id} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex justify-between items-start mb-4">
              <div>
                <h3 className="text-lg font-semibold text-gray-900">{recognition.title}</h3>
                <p className="text-sm text-gray-600">{recognition.institution}</p>
                <p className="text-sm text-gray-500">
                  {new Date(recognition.date).toLocaleDateString()}
                </p>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => handleEdit(recognition)}
                  className="text-blue-600 hover:text-blue-900"
                >
                  <Edit2 size={18} />
                </button>
                <button
                  onClick={() => handleDelete(recognition.id)}
                  className="text-red-600 hover:text-red-900"
                >
                  <Trash2 size={18} />
                </button>
              </div>
            </div>
            <p className="text-gray-700">{recognition.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};