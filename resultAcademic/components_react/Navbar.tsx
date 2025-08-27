import React from 'react';
import { BookOpen, Award, Calendar, Trophy, Lightbulb, Menu, X, Users, LogOut, Settings } from 'lucide-react';
import { useUser } from '../contexts/UserContext';

interface NavItemProps {
  icon: React.ReactNode;
  label: string;
  active: boolean;
  onClick: () => void;
  className?: string;
  variant?: 'default' | 'admin';
}

const NavItem = ({ icon, label, active, onClick, className = '', variant = 'default' }: NavItemProps) => (
  <button
    onClick={onClick}
    className={`flex items-center gap-2 px-4 py-2 rounded-lg transition-colors ${
      variant === 'admin' 
        ? active
          ? 'bg-purple-100 text-purple-700'
          : 'hover:bg-purple-50 text-purple-600'
        : active
          ? 'bg-blue-100 text-blue-700'
          : 'hover:bg-gray-100 text-gray-700'
    } ${className}`}
  >
    {icon}
    <span className="font-medium">{label}</span>
  </button>
);

interface NavbarProps {
  activeSection: string;
  onSectionChange: (section: string) => void;
}

export const Navbar = ({ activeSection, onSectionChange }: NavbarProps) => {
  const [isMenuOpen, setIsMenuOpen] = React.useState(false);
  const { isAdmin, setCurrentUser } = useUser();

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen);
  };

  const handleSectionChange = (section: string) => {
    onSectionChange(section);
    setIsMenuOpen(false);
  };

  const handleLogout = () => {
    setCurrentUser(null);
    onSectionChange('dashboard');
  };

  const mainNavItems = [
    { icon: <BookOpen size={20} />, label: "Publications", id: "publications" },
    { icon: <Award size={20} />, label: "Recognitions", id: "recognitions" },
    { icon: <Calendar size={20} />, label: "Events", id: "events" },
    { icon: <Trophy size={20} />, label: "Awards", id: "awards" },
    { icon: <Lightbulb size={20} />, label: "Patents", id: "patents" },
  ];

  return (
    <nav className="bg-white shadow-sm sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          <div className="flex-shrink-0">
            <h1 className="text-xl font-bold text-gray-900">
              Scientific Achievements
            </h1>
          </div>

          {/* Desktop navigation */}
          <div className="hidden md:flex md:items-center md:gap-2">
            {mainNavItems.map((item) => (
              <NavItem
                key={item.id}
                icon={item.icon}
                label={item.label}
                active={activeSection === item.id}
                onClick={() => handleSectionChange(item.id)}
              />
            ))}
            {isAdmin && (
              <NavItem
                icon={<Users size={20} />}
                label="User Management"
                active={activeSection === 'admin'}
                onClick={() => handleSectionChange('admin')}
                variant="admin"
              />
            )}
            <button
              onClick={handleLogout}
              className="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors text-red-600 hover:bg-red-50 ml-2"
            >
              <LogOut size={20} />
              <span className="font-medium">Logout</span>
            </button>
          </div>

          {/* Mobile menu button and logout */}
          <div className="flex items-center gap-2 md:hidden">
            {isAdmin && (
              <button
                onClick={() => handleSectionChange('admin')}
                className={`inline-flex items-center justify-center p-2 rounded-md ${
                  activeSection === 'admin'
                    ? 'text-purple-700 bg-purple-100'
                    : 'text-purple-600 hover:text-purple-900 hover:bg-purple-50'
                }`}
                aria-label="User Management"
              >
                <Users size={20} />
              </button>
            )}
            <button
              onClick={handleLogout}
              className="inline-flex items-center justify-center p-2 rounded-md text-red-600 hover:text-red-900 hover:bg-red-50"
              aria-label="Logout"
            >
              <LogOut size={20} />
            </button>
            <button
              onClick={toggleMenu}
              className="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100"
            >
              {isMenuOpen ? <X size={24} /> : <Menu size={24} />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile menu */}
      <div className={`md:hidden ${isMenuOpen ? 'block' : 'hidden'}`}>
        <div className="px-2 pt-2 pb-3 space-y-1">
          {mainNavItems.map((item) => (
            <NavItem
              key={item.id}
              icon={item.icon}
              label={item.label}
              active={activeSection === item.id}
              onClick={() => handleSectionChange(item.id)}
              className="w-full"
            />
          ))}
        </div>
      </div>
    </nav>
  );
};