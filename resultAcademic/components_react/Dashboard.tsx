import React from 'react';
import { Award, BookOpen, Calendar, Trophy, Lightbulb } from 'lucide-react';
import type { Publication, Recognition, Event, Award as AwardType, Patent } from '../types';

interface StatCardProps {
  title: string;
  count: number;
  icon: React.ReactNode;
}

const StatCard = ({ title, count, icon }: StatCardProps) => (
  <div className="bg-white rounded-lg shadow p-6">
    <div className="flex items-center justify-between">
      <div>
        <p className="text-sm font-medium text-gray-600">{title}</p>
        <p className="text-2xl font-semibold text-gray-900">{count}</p>
      </div>
      <div className="text-blue-500">{icon}</div>
    </div>
  </div>
);

export const Dashboard = () => {
  // In a real application, these would come from your data store
  const stats = {
    publications: 12,
    recognitions: 5,
    events: 8,
    awards: 3,
    patents: 2,
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Dashboard</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <StatCard
          title="Publications"
          count={stats.publications}
          icon={<BookOpen size={24} />}
        />
        <StatCard
          title="Recognitions"
          count={stats.recognitions}
          icon={<Award size={24} />}
        />
        <StatCard
          title="Events"
          count={stats.events}
          icon={<Calendar size={24} />}
        />
        <StatCard
          title="Awards"
          count={stats.awards}
          icon={<Trophy size={24} />}
        />
        <StatCard
          title="Patents"
          count={stats.patents}
          icon={<Lightbulb size={24} />}
        />
      </div>
    </div>
  );
};